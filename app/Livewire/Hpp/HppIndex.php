<?php

namespace App\Livewire\Hpp;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;

#[Layout('components.layouts.app')]
#[Title('Manajemen HPP & Margin - POS Cafe')]
class HppIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    
    // Quick Edit State
    public $showEditModal = false;
    public $editingProduct = null;
    public $harga_beli = 0;
    public $price = 0;

    // Calculator State
    public $showCalculator = false;
    public $selected_product_id = ''; // ID produk yang dipilih dari database
    public $nama_produk = '';
    public $category_id = '';
    public $tipe_output = 'produk_jadi'; // 'produk_jadi' | 'setengah_jadi'
    public $mode_perhitungan = 'satuan'; // 'satuan' | 'batch'
    public $bahan_baku = [];
    public $alokasi_biaya_tetap = ''; // default kosong agar menampilkan placeholder 0
    public $kenaikan_persen = 0; // slider
    public $selected_tier = 'standar'; // 'kompetitif' | 'standar' | 'premium'

    // Alokasi Biaya Tetap / Operasional Bulanan State
    public $mode_alokasi_ops = 'rincian'; // 'rincian' | 'manual'
    public $target_penjualan_bulanan = 3000; // Estimasi total porsi seluruh menu terjual/bulan (basis pembagi beban tetap)
    public $biaya_tetap_items = [
        ['nama' => 'Biaya Sewa Dapur / Tempat Usaha', 'nominal' => 1000000],
        ['nama' => 'Biaya Listrik, Air & Gas', 'nominal' => 300000],
        ['nama' => 'Biaya Gaji Barista / Karyawan', 'nominal' => 1500000],
        ['nama' => 'Biaya Penyusutan Alat / Mesin Kopi', 'nominal' => 200000],
    ];

    // Target & Proyeksi Penjualan State
    public $target_laba_bulanan = ''; // Default kosong agar user input sendiri
    public $hari_operasional_sebulan = 30; // 30 hari

    public function addBiayaTetapItem()
    {
        $this->mode_alokasi_ops = 'rincian';
        $this->biaya_tetap_items[] = [
            'nama' => '',
            'nominal' => '',
        ];
        $this->updatePriceFromSelectedTier();
    }

    public function removeBiayaTetapItem($index)
    {
        $this->mode_alokasi_ops = 'rincian';
        unset($this->biaya_tetap_items[$index]);
        $this->biaya_tetap_items = array_values($this->biaya_tetap_items);
        $this->updatePriceFromSelectedTier();
    }

    public function resetBiayaTetapPreset()
    {
        $this->mode_alokasi_ops = 'rincian';
        $this->biaya_tetap_items = [
            ['nama' => 'Biaya Sewa Dapur / Tempat Usaha', 'nominal' => 1000000],
            ['nama' => 'Biaya Listrik, Air & Gas', 'nominal' => 300000],
            ['nama' => 'Biaya Gaji Barista / Karyawan', 'nominal' => 1500000],
            ['nama' => 'Biaya Penyusutan Alat / Mesin Kopi', 'nominal' => 200000],
        ];
        $this->updatePriceFromSelectedTier();
        $this->notify('info', 'Preset biaya operasional cafe berhasil dimuat.');
    }

    public function updated($property = null)
    {
        if (!$property) return;

        if (
            str_starts_with($property, 'biaya_tetap_items') ||
            str_starts_with($property, 'bahan_baku') ||
            in_array($property, [
                'target_penjualan_bulanan',
                'kenaikan_persen',
                'mode_alokasi_ops',
                'alokasi_biaya_tetap',
                'selected_tier',
                'target_laba_bulanan',
                'hari_operasional_sebulan',
            ])
        ) {
            $this->updatePriceFromSelectedTier();
        }
    }

    public function updatePriceFromSelectedTier()
    {
        unset($this->hppCalculation);
        unset($this->pricingTiers);
        unset($this->salesProjection);

        $calc = $this->hppCalculation();
        if ($this->mode_alokasi_ops === 'rincian') {
            $this->alokasi_biaya_tetap = $calc['biayaTetap'] > 0 ? $calc['biayaTetap'] : '';
        }

        $tiers = $this->pricingTiers();
        if (!empty($this->selected_tier) && isset($tiers[$this->selected_tier])) {
            $this->price = (float) $tiers[$this->selected_tier]['harga'];
        }
    }

    public function setModeAlokasi($mode)
    {
        $this->mode_alokasi_ops = $mode;
        $this->updatePriceFromSelectedTier();
    }

    public function selectTier($tierKey)
    {
        $this->selected_tier = $tierKey;
        $tiers = $this->pricingTiers();
        if (isset($tiers[$tierKey])) {
            $this->price = (float) $tiers[$tierKey]['harga'];
        }
    }

    public function syncSelectedTier()
    {
        $tiers = $this->pricingTiers();
        if (empty($tiers) || $this->price <= 0) {
            $this->selected_tier = 'standar';
            return;
        }

        $currentPrice = (float) $this->price;
        $closestTier = 'standar';
        $minDiff = PHP_FLOAT_MAX;

        foreach ($tiers as $key => $data) {
            $diff = abs($currentPrice - (float) $data['harga']);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closestTier = $key;
            }
        }

        $this->selected_tier = $closestTier;
    }

    public function updatedSelectedProductId($value)
    {
        if (!empty($value)) {
            $product = Product::with('ingredients')->find($value);
            if ($product) {
                $this->nama_produk = $product->name;
                $this->category_id = $product->category_id;
                $this->price = (float) $product->price;
                $cost = (float) ($product->operational_cost ?? 0);
                $this->alokasi_biaya_tetap = $cost > 0 ? $cost : '';
                $this->mode_alokasi_ops = 'manual'; // Aktifkan mode manual agar langsung menampilkan alokasi tersimpan
                $this->kenaikan_persen = 0;
                $this->selected_tier = 'standar';
                
                if ($product->ingredients->count() > 0) {
                    $this->bahan_baku = [];
                    foreach ($product->ingredients as $ing) {
                        $this->bahan_baku[] = [
                            'nama' => $ing->name,
                            'takaran' => ((float) $ing->amount > 0) ? (float) $ing->amount : '',
                            'satuan_takaran' => $ing->unit,
                            'harga_beli' => ((float) $ing->buy_price > 0) ? (float) $ing->buy_price : '',
                            'jumlah_beli' => ((float) $ing->buy_amount > 0) ? (float) $ing->buy_amount : 1,
                            'satuan_beli' => $ing->buy_unit,
                            'subtotal' => (float) $ing->subtotal,
                        ];
                    }
                    $this->syncSelectedTier();
                    $this->notify('info', 'Memuat resep & biaya operasional tersimpan untuk ' . $product->name);
                } else {
                    $this->bahan_baku = [];
                    $this->addIngredientRow();
                    $this->selected_tier = 'standar';
                    $this->notify('info', 'Produk ' . $product->name . ' dipilih. Silakan isi bahan baku atau gunakan AI.');
                }
            }
        } else {
            $this->selected_product_id = '';
            $this->nama_produk = '';
            $this->category_id = '';
            $this->price = 0;
            $this->alokasi_biaya_tetap = '';
            $this->kenaikan_persen = 0;
            $this->selected_tier = 'standar';
            $this->bahan_baku = [];
            $this->addIngredientRow();
        }
    }

    public function applyPricingTier($tierKey)
    {
        $tiers = $this->pricingTiers();
        if (isset($tiers[$tierKey])) {
            $this->price = $tiers[$tierKey]['harga'];
            $this->selected_tier = $tierKey;
            $this->notify('success', 'Harga jual kasir disetel ke Rp ' . number_format($this->price, 0, ',', '.') . ' (' . ucfirst($tierKey) . ')');
        }
    }

    public function notify($type, $message)
    {
        $this->dispatch('show-toast', type: $type, message: $message);
    }

    public function editProductInCalculator($productId)
    {
        $this->showCalculator = true;
        $this->selected_product_id = $productId;
        $this->updatedSelectedProductId($productId);
    }

    public function openEditModal($productId)
    {
        $this->editProductInCalculator($productId);
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->editingProduct = null;
        $this->harga_beli = 0;
        $this->price = 0;
    }

    public function updateHpp()
    {
        $this->validate([
            'harga_beli' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ], [
            'harga_beli.required' => 'Harga modal (HPP) wajib diisi.',
            'price.required' => 'Harga jual wajib diisi.'
        ]);

        if ($this->editingProduct) {
            $this->editingProduct->update([
                'harga_beli' => (float) $this->harga_beli,
                'price' => (float) $this->price
            ]);

            $this->notify('success', 'HPP & Harga Jual ' . $this->editingProduct->name . ' berhasil diperbarui!');
            $this->closeModal();
        }
    }

    public function toggleCalculator()
    {
        $this->showCalculator = !$this->showCalculator;
        if ($this->showCalculator && empty($this->bahan_baku)) {
            $this->addIngredientRow();
        }
    }

    public function addIngredientRow()
    {
        $this->bahan_baku[] = [
            'nama' => '',
            'takaran' => '',
            'satuan_takaran' => 'gram',
            'harga_beli' => '',
            'jumlah_beli' => 1,
            'satuan_beli' => 'kg',
            'subtotal' => 0
        ];
    }

    public function removeIngredientRow($index)
    {
        unset($this->bahan_baku[$index]);
        $this->bahan_baku = array_values($this->bahan_baku); // reindex
        $this->updatePriceFromSelectedTier();
    }

    public function calculateSubtotal($index)
    {
        if (!isset($this->bahan_baku[$index])) return;

        $row = $this->bahan_baku[$index];
        $amount = !empty($row['takaran']) ? (float) $row['takaran'] : 0;
        $unit = $row['satuan_takaran'] ?? 'gram';
        $buyPrice = !empty($row['harga_beli']) ? (float) $row['harga_beli'] : 0;
        $buyAmount = !empty($row['jumlah_beli']) ? (float) $row['jumlah_beli'] : 1;
        $buyUnit = $row['satuan_beli'] ?? 'kg';

        $pricePerBuyUnit = $buyAmount > 0 ? $buyPrice / $buyAmount : 0;
        
        $multiplier = 1;
        // Standardize conversions for cafe ingredients
        if ($unit == 'gram' && $buyUnit == 'kg') $multiplier = 0.001;
        if ($unit == 'ml' && $buyUnit == 'liter') $multiplier = 0.001;
        if ($unit == 'kg' && $buyUnit == 'gram') $multiplier = 1000;
        if ($unit == 'liter' && $buyUnit == 'ml') $multiplier = 1000;
        
        $subtotal = $amount * $multiplier * $pricePerBuyUnit;
        $this->bahan_baku[$index]['subtotal'] = $subtotal;
        $this->updatePriceFromSelectedTier();
    }

    public function analyzeWithAI()
    {
        $this->validate([
            'nama_produk' => 'required|string|min:3'
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi sebelum analisis AI.'
        ]);

        try {
            \Illuminate\Support\Facades\Log::info('=== [HPP AI DEBUG] START ANALISIS RESEP ===', [
                'nama_produk' => $this->nama_produk,
                'timestamp' => now()->toDateTimeString()
            ]);

            $service = app(\App\Services\GeminiAIService::class);
            $prompt = "Buatkan estimasi takaran bahan standar cafe untuk menu: {$this->nama_produk}.
            ATURAN SATUAN & JUMLAH BELI:
            1. Bahan per butir/lembar/pcs (Paper Filter, Cup, Tutup, Sedotan, Drip Bag, dll):
               - Wajib gunakan 'satuan_takaran': 'pcs'
               - Wajib gunakan 'satuan_beli': 'pcs'
               - Pada 'jumlah_beli': Wajib isi dengan TOTAL ISI PCS dalam 1 kemasan beli (Contoh: Paper Filter 1 pack isi 100 lembar harga 45000 -> harga_beli: 45000, jumlah_beli: 100, satuan_beli: 'pcs'. Cup 1 pack isi 50 pcs harga 30000 -> harga_beli: 30000, jumlah_beli: 50, satuan_beli: 'pcs').
            2. Bahan bubuk/kopi/gula/es:
               - 'satuan_takaran': 'gram'
               - 'satuan_beli': 'kg' (atau 'gram'), 'jumlah_beli': 1 (jika kg) atau 1000 (jika gram)
            3. Bahan cair/susu/sirup/air:
               - 'satuan_takaran': 'ml'
               - 'satuan_beli': 'liter' (atau 'ml'), 'jumlah_beli': 1 (jika liter) atau 1000 (jika ml). Jika air galon 19L harga 20000 -> harga_beli: 20000, jumlah_beli: 19, satuan_beli: 'liter'.

            Gunakan format JSON array dengan keys:
            - nama: nama bahan baku (string)
            - takaran: angka takaran per porsi (number)
            - satuan_takaran: salah satu dari 'gram', 'ml', 'pcs', 'sachet'
            - harga_beli: estimasi harga beli pasaran dalam Rupiah (number)
            - jumlah_beli: total isi kemasan yang dibeli sesuai satuan_beli (number)
            - satuan_beli: salah satu dari 'kg', 'liter', 'gram', 'ml', 'pcs', 'sachet'";
            
            \Illuminate\Support\Facades\Log::info('[HPP AI DEBUG] Mengirim prompt ke Gemini...');
            $startTime = microtime(true);
            
            $data = $service->generateJson($prompt);
            $duration = round(microtime(true) - $startTime, 2);

            \Illuminate\Support\Facades\Log::info('[HPP AI DEBUG] Respons AI diterima!', [
                'durasi_detik' => $duration,
                'jumlah_bahan' => is_array($data) ? count($data) : 0,
                'data_mentah' => $data
            ]);

            if (is_array($data) && count($data) > 0) {
                $this->bahan_baku = [];
                foreach ($data as $idx => $item) {
                    $item['subtotal'] = 0;
                    $item['takaran'] = !empty($item['takaran']) ? $item['takaran'] : '';
                    $item['harga_beli'] = !empty($item['harga_beli']) ? $item['harga_beli'] : '';
                    $this->bahan_baku[] = $item;
                    $this->calculateSubtotal(count($this->bahan_baku) - 1);
                }
                $this->syncSelectedTier();
                $this->updatePriceFromSelectedTier();

                \Illuminate\Support\Facades\Log::info('=== [HPP AI DEBUG] BERHASIL DIMUAT KE FORM ===', [
                    'total_bahan' => count($this->bahan_baku)
                ]);

                $this->notify('success', 'Berhasil! ' . count($this->bahan_baku) . ' bahan baku dimuat untuk ' . $this->nama_produk . ' (waktu: ' . $duration . 's)');
            } else {
                \Illuminate\Support\Facades\Log::warning('[HPP AI DEBUG] Data array kosong / format tidak sesuai');
                $this->notify('error', 'Resep AI kosong. Pastikan nama produk spesifik (misal: "Kopi Susu Gula Aren").');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('=== [HPP AI DEBUG] ERROR TERJADI ===', [
                'pesan_error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine()
            ]);
            $this->notify('error', 'Gagal memproses AI: ' . $e->getMessage());
        }
    }

    #[Computed]
    public function hppCalculation()
    {
        $totalVariable = collect($this->bahan_baku)->sum('subtotal');
        
        $totalBiayaTetapBulanan = collect($this->biaya_tetap_items)->sum(function ($item) {
            return !empty($item['nominal']) ? (float) $item['nominal'] : 0;
        });

        $targetUnits = max(1, (int) $this->target_penjualan_bulanan);

        if ($this->mode_alokasi_ops === 'rincian') {
            $biayaTetap = $totalBiayaTetapBulanan > 0 ? round($totalBiayaTetapBulanan / $targetUnits) : 0;
        } else {
            $biayaTetap = !empty($this->alokasi_biaya_tetap) ? (float) $this->alokasi_biaya_tetap : 0;
        }

        $totalHpp = $totalVariable + $biayaTetap;
        
        $kenaikan = (float) $this->kenaikan_persen;
        $simulatedHpp = ($totalVariable * (1 + $kenaikan / 100)) + $biayaTetap;

        return [
            'totalVariable' => $totalVariable,
            'totalBiayaTetapBulanan' => $totalBiayaTetapBulanan,
            'targetUnits' => $targetUnits,
            'biayaTetap' => $biayaTetap,
            'totalHpp' => $totalHpp,
            'simulatedHpp' => $simulatedHpp
        ];
    }

    #[Computed]
    public function pricingTiers()
    {
        $hppData = $this->hppCalculation();
        $baseHpp = $hppData['simulatedHpp'];

        if ($baseHpp <= 0) return [];

        // Tier Kompetitif: margin ~50%
        // Tier Standar: margin ~58%
        // Tier Premium: margin ~67%

        $kompetitif = ceil(($baseHpp / 0.5) / 500) * 500;
        $standar = ceil(($baseHpp / 0.42) / 500) * 500;
        $premium = ceil(($baseHpp / 0.33) / 500) * 500;

        return [
            'kompetitif' => [
                'harga' => $kompetitif,
                'margin' => (($kompetitif - $baseHpp) / $kompetitif) * 100,
                'profit' => $kompetitif - $baseHpp
            ],
            'standar' => [
                'harga' => $standar,
                'margin' => (($standar - $baseHpp) / $standar) * 100,
                'profit' => $standar - $baseHpp
            ],
            'premium' => [
                'harga' => $premium,
                'margin' => (($premium - $baseHpp) / $premium) * 100,
                'profit' => $premium - $baseHpp
            ]
        ];
    }

    #[Computed]
    public function salesProjection()
    {
        $hppData = $this->hppCalculation();
        $variableCost = (float) ($hppData['totalVariable'] ?? 0);
        $kenaikan = (float) $this->kenaikan_persen;
        $simulatedVariable = $variableCost * (1 + $kenaikan / 100);
        
        $opCostPerUnit = (float) ($hppData['biayaTetap'] ?? 0);
        $unitCost = (float) ($hppData['simulatedHpp'] ?? ($simulatedVariable + $opCostPerUnit));
        
        $sellingPrice = (float) $this->price;
        if ($sellingPrice <= 0) {
            $tiers = $this->pricingTiers();
            $sellingPrice = (float) ($tiers[$this->selected_tier]['harga'] ?? ($tiers['standar']['harga'] ?? 0));
        }
        $targetProfit = (float) $this->target_laba_bulanan;
        $days = (int) $this->hari_operasional_sebulan > 0 ? (int) $this->hari_operasional_sebulan : 30;

        // Margin bersih per porsi (Harga Jual - Biaya Bahan - Biaya Operasional)
        $netMarginPerUnit = max(0, $sellingPrice - $unitCost);

        if ($netMarginPerUnit > 0 && $targetProfit > 0) {
            $totalUnitsMonth = ceil($targetProfit / $netMarginPerUnit);
        } else {
            $totalUnitsMonth = 0;
        }

        $targetUnitsDay = $days > 0 ? (int) ceil($totalUnitsMonth / $days) : 0;
        $potensiOmzet = $totalUnitsMonth * $sellingPrice;
        $totalBiayaProduksi = $totalUnitsMonth * $simulatedVariable;
        $totalBiayaTetap = $totalUnitsMonth * $opCostPerUnit;
        $proyeksiLabaBersih = $potensiOmzet - $totalBiayaProduksi - $totalBiayaTetap;

        return [
            'variableCost' => $simulatedVariable,
            'opCostPerUnit' => $opCostPerUnit,
            'unitCost' => $unitCost,
            'sellingPrice' => $sellingPrice,
            'unitMargin' => $netMarginPerUnit,
            'netMarginPerUnit' => $netMarginPerUnit,
            'targetProfit' => $targetProfit,
            'days' => $days,
            'targetUnitsDay' => $targetUnitsDay,
            'totalUnitsMonth' => $totalUnitsMonth,
            'potensiOmzet' => $potensiOmzet,
            'totalBiayaProduksi' => $totalBiayaProduksi,
            'totalBiayaTetap' => $totalBiayaTetap,
            'proyeksiLabaBersih' => $proyeksiLabaBersih,
        ];
    }

    public function saveCalculation()
    {
        $this->validate([
            'nama_produk' => 'required|string|max:255',
            'bahan_baku' => 'required|array|min:1',
            'bahan_baku.*.nama' => 'required|string',
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'bahan_baku.required' => 'Komponen bahan baku minimal 1 item.',
            'bahan_baku.*.nama.required' => 'Nama bahan baku tidak boleh kosong.'
        ]);

        $hppData = $this->hppCalculation();
        $totalHpp = (float) $hppData['simulatedHpp'];
        $tiers = $this->pricingTiers();

        // Cari produk berdasarkan ID yang dipilih atau berdasarkan Nama
        $product = null;
        if (!empty($this->selected_product_id)) {
            $product = Product::find($this->selected_product_id);
        }
        if (!$product) {
            $product = Product::where('name', $this->nama_produk)->first();
        }

        $isNewProduct = false;

        if (!$product) {
            // BUAT PRODUK BARU OTOMATIS
            $isNewProduct = true;
            $categoryId = $this->category_id ?: (Category::first()?->id ?? 1);
            
            // Tentukan harga jual awal jika belum diset
            $sellingPrice = (float) $this->price;
            if ($sellingPrice <= 0) {
                $sellingPrice = $tiers['standar']['harga'] ?? ($totalHpp > 0 ? ceil(($totalHpp * 1.5) / 500) * 500 : 15000);
            }

            // Generate SKU unik
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $this->nama_produk);
            $skuPrefix = strtoupper(substr($cleanName, 0, 3));
            if (strlen($skuPrefix) < 3) $skuPrefix = 'CFE';
            $sku = $skuPrefix . '-' . rand(1000, 9999);

            $product = Product::create([
                'name' => $this->nama_produk,
                'category_id' => $categoryId,
                'sku' => $sku,
                'harga_beli' => $totalHpp,
                'operational_cost' => (float) ($hppData['biayaTetap'] ?? 0),
                'price' => $sellingPrice,
                'description' => 'Menu racikan via Kalkulator HPP & AI Pricing Strategy',
            ]);
        } else {
            // UPDATE PRODUK YANG SUDAH ADA
            $updateData = [
                'name' => $this->nama_produk,
                'harga_beli' => $totalHpp,
                'operational_cost' => (float) ($hppData['biayaTetap'] ?? 0),
            ];
            
            if (!empty($this->category_id)) {
                $updateData['category_id'] = $this->category_id;
            }
            if ((float) $this->price > 0) {
                $updateData['price'] = (float) $this->price;
            }

            $product->update($updateData);
        }

        // Simpan komponen resep bahan baku
        \App\Models\ProductIngredient::where('product_id', $product->id)->delete();
        
        foreach ($this->bahan_baku as $bahan) {
            if (empty($bahan['nama'])) continue;
            
            \App\Models\ProductIngredient::create([
                'product_id' => $product->id,
                'name' => $bahan['nama'],
                'amount' => (float) ($bahan['takaran'] ?? 0),
                'unit' => $bahan['satuan_takaran'] ?? 'gram',
                'buy_price' => (float) ($bahan['harga_beli'] ?? 0),
                'buy_amount' => (float) ($bahan['jumlah_beli'] ?? 1),
                'buy_unit' => $bahan['satuan_beli'] ?? 'kg',
                'subtotal' => (float) ($bahan['subtotal'] ?? 0),
            ]);
        }

        if ($isNewProduct) {
            $this->notify('success', '✨ Menu baru "' . $product->name . '" berhasil ditambahkan ke database dengan HPP Rp ' . number_format($totalHpp, 0, ',', '.') . ' dan Harga Jual Rp ' . number_format($product->price, 0, ',', '.') . '!');
        } else {
            $this->notify('success', '✅ Resep dan HPP untuk "' . $product->name . '" berhasil diperbarui!');
        }

        $this->toggleCalculator();
    }

    public function render()
    {
        $query = Product::query()->with('category');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', $this->search)
                    ->orWhere('barcode', $this->search);
            });
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        $allProducts = Product::all();
        $totalMenuCount = $allProducts->count();
        
        $totalMarginPercent = 0;
        $totalFoodCostPercent = 0;
        $lowMarginCount = 0;
        $totalProfitSum = 0;

        foreach ($allProducts as $p) {
            $hpp = (float) $p->harga_beli;
            $jual = (float) $p->price;
            $profit = max(0, $jual - $hpp);
            $margin = $jual > 0 ? ($profit / $jual) * 100 : 0;
            $foodCost = $jual > 0 ? ($hpp / $jual) * 100 : 0;

            $totalMarginPercent += $margin;
            $totalFoodCostPercent += $foodCost;
            $totalProfitSum += $profit;

            if ($margin < 35 && $jual > 0) {
                $lowMarginCount++;
            }
        }

        $avgMargin = $totalMenuCount > 0 ? ($totalMarginPercent / $totalMenuCount) : 0;
        $avgFoodCost = $totalMenuCount > 0 ? ($totalFoodCostPercent / $totalMenuCount) : 0;
        $avgProfitPerItem = $totalMenuCount > 0 ? ($totalProfitSum / $totalMenuCount) : 0;

        $products = $query->orderBy('name', 'asc')->paginate(12);
        $categories = Category::all();

        return view('livewire.hpp.hpp-index', [
            'products' => $products,
            'allProducts' => $allProducts,
            'categories' => $categories,
            'totalMenuCount' => $totalMenuCount,
            'avgMargin' => $avgMargin,
            'avgFoodCost' => $avgFoodCost,
            'lowMarginCount' => $lowMarginCount,
            'avgProfitPerItem' => $avgProfitPerItem
        ]);
    }
}
