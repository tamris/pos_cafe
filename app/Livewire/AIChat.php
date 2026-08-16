<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\GeminiAIService;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Category;
use App\Models\CashierShift;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIChat extends Component
{
    public $isOpen = false;
    public $message = '';
    public $messages = [];
    public $isLoading = false;
    public $isTyping = false;

    protected $listeners = [
        'toggleChat',
        'add-bot-message-from-js' => 'addBotMessageFromJs'
    ];

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
        if ($this->isOpen && empty($this->messages)) {
            $shopName = Setting::first()?->shop_name ?? 'POS Cafe';
            $this->addBotMessage("☕ Halo! Saya Asisten AI **{$shopName}**.\n\nAda yang bisa saya bantu terkait **analisis penjualan menu cafe**, **omset & profit**, **performa shift kasir**, atau **stok menu** hari ini?");
        }
    }

    public function sendQuickPrompt($promptText)
    {
        $this->message = $promptText;
        $this->sendMessage();
    }

    public function sendMessage()
    {
        if (trim($this->message) === '') {
            return;
        }

        // Simpan pesan user dan langsung tampilkan
        $userMessage = $this->message;
        $this->addUserMessage($userMessage);
        $this->message = '';
        $this->isLoading = true;
        $this->isTyping = true;

        Log::info('📤 [AIChat] Sending message', ['message' => $userMessage]);

        // Dispatch event untuk scroll ke bawah setelah pesan user ditambahkan
        $this->dispatch('scroll-to-bottom');

        // Gunakan queue atau async untuk memproses AI response
        $this->processAIResponse($userMessage);
    }

    private function processAIResponse($userMessage)
    {
        try {
            $geminiService = app(GeminiAIService::class);
            $context = $this->prepareContext($userMessage);

            Log::info('🤖 [AIChat] Processing AI response');

            // Dapatkan response dari AI
            $response = $geminiService->generateResponse($userMessage, $context);

            Log::info('📥 [AIChat] AI Response received', [
                'response_length' => strlen($response)
            ]);

            // Tambahkan pesan bot dengan animasi mengetik
            $this->dispatch('start-typing-animation', message: $response);
        } catch (\Exception $e) {
            Log::error('💥 [AIChat] Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->addBotMessage('Maaf, terjadi kesalahan: ' . $e->getMessage());
            $this->isLoading = false;
            $this->isTyping = false;
        }
    }

    // Method untuk menerima pesan dari JavaScript (typing animation selesai)
    public function addBotMessageFromJs($message)
    {
        $this->messages[] = [
            'type' => 'bot',
            'content' => $message,
            'time' => now()->format('H:i'),
        ];

        $this->isTyping = false;
        $this->isLoading = false;
        $this->dispatch('scroll-to-bottom');
    }

    public function addBotMessage($message)
    {
        $this->messages[] = [
            'type' => 'bot',
            'content' => $message,
            'time' => now()->format('H:i'),
        ];

        $this->dispatch('scroll-to-bottom');
    }

    private function addUserMessage($message)
    {
        $this->messages[] = [
            'type' => 'user',
            'content' => $message,
            'time' => now()->format('H:i'),
        ];
    }

    private function prepareContext($message)
    {
        $context = '';
        $message = strtolower($message);

        try {
            // Deteksi tanggal spesifik
            $specificDate = $this->extractDate($message);

            if ($specificDate) {
                $context = $this->getContextByDate($specificDate, $message);
            } elseif ($this->isShiftRelated($message)) {
                $context = $this->getShiftContext();
            } elseif ($this->isProfitRelated($message)) {
                $context = $this->getProfitContext();
            } elseif ($this->isSalesRelated($message)) {
                $context = $this->getSalesContext();
            } elseif ($this->isProductRelated($message)) {
                $context = $this->getProductContext();
            } elseif ($this->isTransactionRelated($message)) {
                $context = $this->getTransactionContext();
            } else {
                // Default context: ringkasan umum cafe hari ini
                $context = $this->getSalesContext();
            }
        } catch (\Exception $e) {
            Log::error('Error preparing context', ['error' => $e->getMessage()]);
        }

        return $context;
    }

    private function isShiftRelated($message)
    {
        $keywords = ['shift', 'kasir', 'buka shift', 'tutup shift', 'laci', 'uang kas', 'kas awal', 'modal', 'selisih', 'rekap shift', 'jaga'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isProfitRelated($message)
    {
        $keywords = ['profit', 'untung', 'keuntungan', 'laba', 'margin', 'berapa profit', 'total profit', 'hpp', 'biaya'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isSalesRelated($message)
    {
        $keywords = ['penjualan', 'jual', 'laporan', 'pendapatan', 'revenue', 'omzet', 'terjual', 'transaksi', 'hasil', 'kinerja', 'cup', 'porsi', 'laris', 'favorit'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isProductRelated($message)
    {
        $keywords = ['produk', 'menu', 'kopi', 'minuman', 'makanan', 'snack', 'stok', 'inventory', 'kategori', 'item', 'terlaris', 'apa menu'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isTransactionRelated($message)
    {
        $keywords = [
            'transaksi', 'pembelian', 'invoice', 'struk', 'pembayaran', 'beli',
            'detail transaksi', 'transaksi hari', 'transaksi terbaru', 'invoice nomor',
            'transaksi terakhir', 'metode pembayaran', 'cash', 'transfer', 'qris', 'meja', 'dine in', 'take away', 'delivery'
        ];
        return $this->containsKeywords($message, $keywords);
    }

    private function containsKeywords($message, $keywords)
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, strtolower($keyword))) {
                return true;
            }
        }
        return false;
    }

    private function getShiftContext()
    {
        try {
            $activeShifts = CashierShift::with('user')->where('status', 'open')->get();
            $todayShifts = CashierShift::with('user')->whereDate('start_time', today())->get();
            $totalClosedToday = $todayShifts->where('status', 'closed')->count();
            $totalCashSales = $todayShifts->sum('cash_sales');
            $totalNonCashSales = $todayShifts->sum('qris_sales') + $todayShifts->sum('transfer_sales');
            $totalDifference = $todayShifts->where('status', 'closed')->sum('difference');

            $context = "=== DATA MANAJEMEN SHIFT KASIR CAFE ===\n\n";

            $context .= "STATUS SHIFT AKTIF SAAT INI:\n";
            if ($activeShifts->count() > 0) {
                foreach ($activeShifts as $shift) {
                    $context .= "🟢 Shift Aktif (#SFT-" . str_pad($shift->id, 5, '0', STR_PAD_LEFT) . "):\n";
                    $context .= "   - Kasir Bertugas: {$shift->user->name}\n";
                    $context .= "   - Waktu Buka: " . ($shift->start_time ? $shift->start_time->format('H:i') : '-') . "\n";
                    $context .= "   - Modal Kas Awal di Laci: Rp " . number_format($shift->starting_cash, 0, ',', '.') . "\n";
                    $context .= "   - Penjualan Tunai Selama Shift: Rp " . number_format($shift->cash_sales, 0, ',', '.') . "\n";
                    $context .= "   - Total Kas Laci Diharapkan Saat Ini: Rp " . number_format($shift->expected_cash, 0, ',', '.') . "\n";
                    $context .= "   - Penjualan Non-Tunai (QRIS & Transfer): Rp " . number_format($shift->qris_sales + $shift->transfer_sales, 0, ',', '.') . "\n";
                    $context .= "   - Total Transaksi: {$shift->total_transactions} nota\n";
                }
            } else {
                $context .= "- Saat ini TIDAK ADA shift kasir yang sedang aktif terbuka.\n";
            }
            $context .= "\n";

            $context .= "REKAPITULASI SHIFT HARI INI:\n";
            $context .= "- Total Sesi Shift: " . $todayShifts->count() . " shift (" . $totalClosedToday . " ditutup selesai)\n";
            $context .= "- Total Uang Tunai Masuk Laci Kasir: Rp " . number_format($totalCashSales, 0, ',', '.') . "\n";
            $context .= "- Total Non-Tunai (QRIS & Transfer): Rp " . number_format($totalNonCashSales, 0, ',', '.') . "\n";
            $context .= "- Total Selisih Kas Fisik: Rp " . number_format($totalDifference, 0, ',', '.') . " (" . ($totalDifference == 0 ? 'Seimbang/Pas Rp 0' : ($totalDifference < 0 ? 'Terdeteksi selisih kurang' : 'Terdeteksi selisih lebih')) . ")\n\n";

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting shift context', ['error' => $e->getMessage()]);
            return "Data shift kasir tidak dapat diakses saat ini.";
        }
    }

    private function getSalesContext()
    {
        try {
            $todayTransactions = Transaction::whereDate('created_at', today())->count();
            $todayRevenue = Transaction::whereDate('created_at', today())->sum('total');
            $yesterdayRevenue = Transaction::whereDate('created_at', today()->subDay())->sum('total');
            $weekRevenue = Transaction::where('created_at', '>=', now()->startOfWeek())->sum('total');
            $monthRevenue = Transaction::whereMonth('created_at', now()->month)->sum('total');

            // Total Cups/Porsi terjual hari ini
            $todayItemsSold = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.created_at', today())
                ->sum('transaction_details.quantity');

            // Breakdown Tipe Pesanan Hari Ini
            $orderTypes = Transaction::whereDate('created_at', today())
                ->select('order_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
                ->groupBy('order_type')
                ->get();

            $lowStockProducts = Product::where('stock', '<', 10)->get();
            $totalProducts = Product::count();
            $totalCategories = Category::count();

            // Top Menu Cafe Terlaris
            $topProducts = Product::select(
                'products.*',
                DB::raw('COALESCE(SUM(transaction_details.quantity), 0) as total_sold'),
                DB::raw('COALESCE(SUM(transaction_details.subtotal), 0) as total_revenue')
            )
                ->leftJoin('transaction_details', 'products.id', '=', 'transaction_details.product_id')
                ->groupBy('products.id')
                ->orderBy('total_sold', 'DESC')
                ->take(8)
                ->get();

            // Metode pembayaran
            $paymentMethods = Transaction::select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
                ->whereDate('created_at', today())
                ->groupBy('payment_method')
                ->get();

            $context = "=== LAPORAN PENJUALAN & OPERASIONAL CAFE ===\n\n";

            $context .= "RINGKASAN OMSET & TRANSAKSI HARI INI:\n";
            $context .= "- Total Omset Hari Ini: Rp " . number_format($todayRevenue, 0, ',', '.') . "\n";
            $context .= "- Total Transaksi: {$todayTransactions} nota struk\n";
            $context .= "- Total Menu/Cup Terjual: " . number_format($todayItemsSold, 0, ',', '.') . " porsi/cup\n";
            $context .= "- Rata-rata per Nota (AOV): Rp " . ($todayTransactions > 0 ? number_format($todayRevenue / $todayTransactions, 0, ',', '.') : '0') . "\n";
            $context .= "- Omset Kemarin: Rp " . number_format($yesterdayRevenue, 0, ',', '.') . "\n";
            $context .= "- Omset Minggu Ini: Rp " . number_format($weekRevenue, 0, ',', '.') . "\n";
            $context .= "- Omset Bulan Ini: Rp " . number_format($monthRevenue, 0, ',', '.') . "\n\n";

            if ($orderTypes->count() > 0) {
                $context .= "MODE PESANAN CAFE HARI INI:\n";
                foreach ($orderTypes as $ot) {
                    $modeLabel = match ($ot->order_type) {
                        'dine_in' => '🍽️ Makan di Tempat (Dine In)',
                        'take_away' => '🛍️ Bawa Pulang (Take Away)',
                        'delivery' => '🚚 Pesan Antar (Delivery)',
                        default => $ot->order_type,
                    };
                    $context .= "- {$modeLabel}: {$ot->count} pesanan (Rp " . number_format($ot->total, 0, ',', '.') . ")\n";
                }
                $context .= "\n";
            }

            if ($topProducts->count() > 0) {
                $context .= "TOP MENU CAFE TERLARIS:\n";
                foreach ($topProducts as $index => $product) {
                    $context .= ($index + 1) . ". {$product->name} (Harga: Rp " . number_format($product->price, 0, ',', '.') . "):\n";
                    $context .= "   - Terjual: {$product->total_sold} cup/porsi\n";
                    $context .= "   - Total Omset: Rp " . number_format($product->total_revenue, 0, ',', '.') . "\n";
                    $context .= "   - Sisa Stok Bahan/Menu: {$product->stock} unit\n";
                }
                $context .= "\n";
            }

            if ($paymentMethods->count() > 0) {
                $context .= "METODE PEMBAYARAN HARI INI:\n";
                foreach ($paymentMethods as $method) {
                    $context .= "- " . strtoupper($method->payment_method) . ": {$method->count} transaksi (Rp " . number_format($method->total, 0, ',', '.') . ")\n";
                }
                $context .= "\n";
            }

            if ($lowStockProducts->count() > 0) {
                $context .= "⚠️ PERINGATAN STOK MENU/BAHAN MENIPIS (<10):\n";
                foreach ($lowStockProducts as $p) {
                    $context .= "- {$p->name}: sisa {$p->stock} unit\n";
                }
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting sales context', ['error' => $e->getMessage()]);
            return "Data penjualan cafe tidak dapat diakses saat ini.";
        }
    }

    private function getProductContext()
    {
        try {
            $products = Product::with('category')->get();
            $lowStockProducts = Product::where('stock', '<', 10)->get();
            $outOfStockProducts = Product::where('stock', '=', 0)->get();
            $categories = Category::withCount('products')->get();

            $context = "=== DAFTAR MENU & KATALOG CAFE ===\n\n";

            $context .= "RINGKASAN MENU & KATEGORI:\n";
            $context .= "- Total Menu: " . $products->count() . " produk menu\n";
            $context .= "- Total Kategori: " . $categories->count() . " kategori\n";
            $context .= "- Menu Stok Menipis: " . $lowStockProducts->count() . "\n";
            $context .= "- Menu Habis (Sold Out): " . $outOfStockProducts->count() . "\n\n";

            $context .= "KATEGORI MENU CAFE:\n";
            foreach ($categories as $category) {
                $catProducts = $products->where('category_id', $category->id);
                $context .= "📁 Kategori {$category->name} ({$category->products_count} menu):\n";
                foreach ($catProducts as $cp) {
                    $margin = $cp->price > 0 ? round((($cp->price - ($cp->harga_beli ?? 0)) / $cp->price) * 100, 1) : 0;
                    $context .= "   - {$cp->name} | Jual: Rp " . number_format($cp->price, 0, ',', '.') . " | HPP: Rp " . number_format($cp->harga_beli ?? 0, 0, ',', '.') . " | Margin: {$margin}% | Stok: {$cp->stock}\n";
                }
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting product context', ['error' => $e->getMessage()]);
            return "Data menu cafe tidak dapat diakses saat ini.";
        }
    }

    private function getTransactionContext()
    {
        try {
            $todayTransactions = Transaction::whereDate('created_at', today())->count();
            $todayRevenue = Transaction::whereDate('created_at', today())->sum('total');

            $recentTransactions = Transaction::with(['user', 'details.product'])
                ->latest()
                ->take(10)
                ->get();

            $context = "=== DATA TRANSAKSI & STRUK CAFE ===\n\n";
            $context .= "RINGKASAN HARI INI: {$todayTransactions} transaksi (Total Omset: Rp " . number_format($todayRevenue, 0, ',', '.') . ")\n\n";

            $context .= "10 TRANSAKSI TERBARU:\n";
            foreach ($recentTransactions as $index => $t) {
                $itemsSummary = $t->details->map(function ($d) {
                    return ($d->product->name ?? 'Item') . ' (' . $d->quantity . 'x)';
                })->implode(', ');

                $mode = match ($t->order_type) {
                    'dine_in' => 'Dine In' . ($t->table_number ? ' (Meja ' . $t->table_number . ')' : ''),
                    'take_away' => 'Take Away',
                    'delivery' => 'Delivery',
                    default => $t->order_type ?? 'POS',
                };

                $context .= ($index + 1) . ". {$t->invoice_number} | " . $t->created_at->format('H:i') . " | Kasir: " . ($t->user->name ?? '-') . " | {$mode}\n";
                $context .= "   - Total: Rp " . number_format($t->total, 0, ',', '.') . " [{$t->payment_method}]\n";
                $context .= "   - Menu: {$itemsSummary}\n";
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting transaction context', ['error' => $e->getMessage()]);
            return "Data transaksi cafe tidak dapat diakses saat ini.";
        }
    }

    private function getProfitContext()
    {
        try {
            $todayProfit = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.created_at', today())
                ->sum('transaction_details.profit');

            $todayRevenue = Transaction::whereDate('created_at', today())->sum('total');
            $yesterdayProfit = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.created_at', today()->subDay())
                ->sum('transaction_details.profit');

            $monthProfit = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->whereMonth('transactions.created_at', now()->month)
                ->sum('transaction_details.profit');

            $profitMargin = $todayRevenue > 0 ? round(($todayProfit / $todayRevenue) * 100, 2) : 0;

            $context = "=== DATA PROFIT & MARGIN KEUNTUNGAN CAFE ===\n\n";
            $context .= "RINGKASAN KEUNTUNGAN (PROFIT BERSIH):\n";
            $context .= "- Profit Bersih Hari Ini: Rp " . number_format($todayProfit, 0, ',', '.') . "\n";
            $context .= "- Total Omset Hari Ini: Rp " . number_format($todayRevenue, 0, ',', '.') . "\n";
            $context .= "- Margin Keuntungan Hari Ini: {$profitMargin}%\n";
            $context .= "- Profit Kemarin: Rp " . number_format($yesterdayProfit, 0, ',', '.') . "\n";
            $context .= "- Total Profit Bulan Ini: Rp " . number_format($monthProfit, 0, ',', '.') . "\n\n";

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting profit context', ['error' => $e->getMessage()]);
            return "Data profit cafe tidak dapat diakses saat ini.";
        }
    }

    private function extractDate($message)
    {
        try {
            $patterns = [
                '/(\d{1,2})\s+(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember|jan|feb|mar|apr|mei|jun|jul|agt|sep|okt|nov|des)\s+(\d{4})/i',
                '/(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})/',
                '/(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/',
            ];

            $monthMap = [
                'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
                'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
                'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
                'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
                'jun' => '06', 'jul' => '07', 'agt' => '08', 'sep' => '09',
                'okt' => '10', 'nov' => '11', 'des' => '12'
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $message, $matches)) {
                    if (count($matches) === 4) {
                        if (isset($monthMap[strtolower($matches[2])])) {
                            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                            $month = $monthMap[strtolower($matches[2])];
                            $year = $matches[3];
                            return \Carbon\Carbon::parse("$year-$month-$day");
                        }
                        if (strlen($matches[1]) === 4) {
                            return \Carbon\Carbon::parse($matches[1] . '-' . $matches[2] . '-' . $matches[3]);
                        } else {
                            return \Carbon\Carbon::parse($matches[3] . '-' . $matches[2] . '-' . $matches[1]);
                        }
                    }
                }
            }

            if (str_contains($message, 'kemarin')) {
                return \Carbon\Carbon::yesterday();
            }
            if (str_contains($message, 'hari ini') || str_contains($message, 'today')) {
                return \Carbon\Carbon::today();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error extracting date', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function getContextByDate($date, $originalMessage)
    {
        try {
            $dateStr = $date->format('d F Y');
            $dateForDb = $date->format('Y-m-d');

            $transactions = Transaction::whereDate('created_at', $dateForDb)->count();
            $revenue = Transaction::whereDate('created_at', $dateForDb)->sum('total');

            $profit = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.created_at', $dateForDb)
                ->sum('transaction_details.profit');

            $context = "=== DATA PENJUALAN CAFE TANGGAL {$dateStr} ===\n\n";

            if ($transactions > 0) {
                $context .= "📊 RINGKASAN TANGGAL {$dateStr}:\n";
                $context .= "✅ Total Transaksi: {$transactions} nota\n";
                $context .= "✅ Total Omset: Rp " . number_format($revenue, 0, ',', '.') . "\n";
                $context .= "✅ Total Profit Bersih: Rp " . number_format($profit, 0, ',', '.') . "\n";
                if ($revenue > 0) {
                    $context .= "✅ Margin Keuntungan: " . number_format(($profit / $revenue) * 100, 2) . "%\n";
                }
            } else {
                $context .= "❌ TIDAK ADA TRANSAKSI pada tanggal {$dateStr}.\n";
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting context by date', ['error' => $e->getMessage()]);
            return "Maaf, terjadi kesalahan saat mengambil data untuk tanggal tersebut.";
        }
    }

    public function render()
    {
        return view('livewire.ai-chat');
    }
}
