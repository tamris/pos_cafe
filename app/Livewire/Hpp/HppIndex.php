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
#[Title('Manajemen HPP & Margin - Cafe Noli')]
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
    public $alokasi_biaya_tetap = 3800; // default
    public $kenaikan_persen = 0; // slider
    public $selected_tier = 'standar'; // 'kompetitif' | 'standar' | 'premium'

    public function selectTier($tierKey)
    {
        $this->selected_tier = $tierKey;
        $tiers = $this->pricingTiers();
        if (isset($tiers[$tierKey])) {
            $this->price = (float) $tiers[$tierKey]['harga'];
        }
    }

    public function updatedSelectedProductId($value)
    {
        if (!empty($value)) {
            $product = Product::with('ingredients')->find($value);
            if ($product) {
                $this->nama_produk = $product->name;
                $this->category_id = $product->category_id;
                $this->price = (float) $product->price;
                $this->alokasi_biaya_tetap = 3800;
                
                if ($product->ingredients->count() > 0) {
                    $this->bahan_baku = [];
                    foreach ($product->ingredients as $ing) {
                        $this->bahan_baku[] = [
                            'nama' => $ing->name,
                            'takaran' => (float) $ing->amount,
                            'satuan_takaran' => $ing->unit,
                            'harga_beli' => (float) $ing->buy_price,
                            'jumlah_beli' => (float) $ing->buy_amount,
                            'satuan_beli' => $ing->buy_unit,
                            'subtotal' => (float) $ing->subtotal,
                        ];
                    }
                    $this->notify('info', 'Memuat resep bahan baku yang tersimpan untuk ' . $product->name);
                } else {
                    $this->bahan_baku = [];
                    $this->addIngredientRow();
                    $this->notify('info', 'Produk ' . $product->name . ' dipilih. Silakan isi bahan baku atau gunakan AI.');
                }
            }
        } else {
            $this->selected_product_id = '';
            $this->nama_produk = '';
            $this->category_id = '';
            $this->price = 0;
            $this->bahan_baku = [];
            $this->addIngredientRow();
        }
    }

    public function applyPricingTier($tierKey)
    {
        $tiers = $this->pricingTiers();
        if (isset($tiers[$tierKey])) {
            $this->price = $tiers[$tierKey]['harga'];
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
            'takaran' => 0,
            'satuan_takaran' => 'gram',
            'harga_beli' => 0,
            'jumlah_beli' => 1,
            'satuan_beli' => 'kg',
            'subtotal' => 0
        ];
    }

    public function removeIngredientRow($index)
    {
        unset($this->bahan_baku[$index]);
        $this->bahan_baku = array_values($this->bahan_baku); // reindex
    }

    public function calculateSubtotal($index)
    {
        if (!isset($this->bahan_baku[$index])) return;

        $row = $this->bahan_baku[$index];
        $amount = (float) ($row['takaran'] ?? 0);
        $unit = $row['satuan_takaran'] ?? 'gram';
        $buyPrice = (float) ($row['harga_beli'] ?? 0);
        $buyAmount = (float) ($row['jumlah_beli'] ?? 1);
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
    }

    public function analyzeWithAI()
    {
        $this->validate([
            'nama_produk' => 'required|string|min:3'
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi sebelum analisis AI.'
        ]);

        try {
            $service = app(\App\Services\GeminiAIService::class);
            $prompt = "Buatkan estimasi takaran bahan standar cafe untuk menu: {$this->nama_produk}. 
            Kembalikan HANYA dalam format JSON array yang valid, tanpa markdown, tanpa penjelasan lain.
            Gunakan satuan standar cafe:
            - Satuan takaran porsi: 'gram', 'ml', 'pcs', atau 'sachet'.
            - Satuan pembelian: 'kg', 'liter', 'pack', 'pcs', 'botol', atau 'kaleng'.
            Struktur JSON array:
            [
                {\"nama\": \"Bahan A\", \"takaran\": 15, \"satuan_takaran\": \"gram\", \"harga_beli\": 85000, \"jumlah_beli\": 1, \"satuan_beli\": \"kg\"}
            ]";
            
            $response = $service->generateResponse($prompt);
            
            // Bersihkan markdown jika AI mengembalikan markdown format (misal ```json ... ```)
            $response = preg_replace('/```json|```/', '', $response);
            $response = trim($response);

            $data = json_decode($response, true);

            if (is_array($data) && count($data) > 0) {
                $this->bahan_baku = [];
                foreach ($data as $item) {
                    $item['subtotal'] = 0;
                    $this->bahan_baku[] = $item;
                    $this->calculateSubtotal(count($this->bahan_baku) - 1);
                }
                $this->notify('success', 'Berhasil mendapatkan rekomendasi resep AI untuk ' . $this->nama_produk);
            } else {
                $this->notify('error', 'Gagal mem-parsing respons AI. Pastikan nama produk spesifik (misal: "Kopi Susu Gula Aren").');
            }
        } catch (\Exception $e) {
            $this->notify('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    #[Computed]
    public function hppCalculation()
    {
        $totalVariable = collect($this->bahan_baku)->sum('subtotal');
        $biayaTetap = (float) $this->alokasi_biaya_tetap;
        $totalHpp = $totalVariable + $biayaTetap;
        
        $kenaikan = (float) $this->kenaikan_persen;
        $simulatedHpp = ($totalVariable * (1 + $kenaikan / 100)) + $biayaTetap;

        return [
            'totalVariable' => $totalVariable,
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
                'price' => $sellingPrice,
                'stock' => 0,
                'description' => 'Menu racikan via Kalkulator HPP & AI Pricing Strategy',
            ]);
        } else {
            // UPDATE PRODUK YANG SUDAH ADA
            $updateData = [
                'name' => $this->nama_produk,
                'harga_beli' => $totalHpp,
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
