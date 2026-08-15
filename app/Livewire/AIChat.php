<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\GeminiAIService;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Category;
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
            $this->addBotMessage('Halo! Saya asisten Toko Kendali Store. Ada yang bisa saya bantu mengenai penjualan atau produk?');
        }
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

    private function isProfitRelated($message)
    {
        $keywords = ['profit', 'untung', 'keuntungan', 'laba', 'margin', 'berapa profit', 'total profit'];
        return $this->containsKeywords($message, $keywords);
    }

    // ... (method-method helper lainnya tetap sama: prepareContext, isSalesRelated, dll.)
    private function prepareContext($message)
    {
        $context = '';
        $message = strtolower($message);

        try {
            // 🆕 Deteksi tanggal spesifik
            $specificDate = $this->extractDate($message);

            if ($specificDate) {
                // Jika ada tanggal spesifik, gunakan context berdasarkan tanggal tersebut
                $context = $this->getContextByDate($specificDate, $message);
            } elseif ($this->isProfitRelated($message)) {
                $context = $this->getProfitContext();
            } elseif ($this->isSalesRelated($message)) {
                $context = $this->getSalesContext();
            } elseif ($this->isProductRelated($message)) {
                $context = $this->getProductContext();
            } elseif ($this->isTransactionRelated($message)) {
                $context = $this->getTransactionContext();
            }
        } catch (\Exception $e) {
            Log::error('Error preparing context', ['error' => $e->getMessage()]);
        }

        return $context;
    }

    private function isSalesRelated($message)
    {
        $keywords = ['penjualan', 'jual', 'laporan', 'pendapatan', 'revenue', 'omzet', 'terjual', 'transaksi', 'hasil', 'kinerja'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isProductRelated($message)
    {
        $keywords = ['produk', 'barang', 'stok', 'inventory', 'kategori', 'item', 'terlaris', 'apa produk'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isTransactionRelated($message)
    {
        $keywords = [
            'transaksi',
            'pembelian',
            'invoice',
            'struk',
            'pembayaran',
            'beli',
            'detail transaksi',
            'transaksi hari',
            'transaksi terbaru',
            'invoice nomor',
            'transaksi terakhir',
            'pembelian hari ini',
            'transaksi bulan',
            'metode pembayaran',
            'cash',
            'transfer',
            'qris',
            'kembalian'
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

    private function getSalesContext()
    {
        try {
            // Statistik umum
            $todayTransactions = Transaction::whereDate('created_at', today())->count();
            $todayRevenue = Transaction::whereDate('created_at', today())->sum('total');
            $yesterdayRevenue = Transaction::whereDate('created_at', today()->subDay())->sum('total');
            $weekRevenue = Transaction::where('created_at', '>=', now()->startOfWeek())->sum('total');
            $monthRevenue = Transaction::whereMonth('created_at', now()->month)->sum('total');

            $lowStockProducts = Product::where('stock', '<', 10)->get();
            $outOfStockProducts = Product::where('stock', '=', 0)->get();
            $totalProducts = Product::count();
            $totalCategories = Category::count();

            // Produk terlaris dengan detail
            $topProducts = Product::select(
                'products.*',
                DB::raw('COALESCE(SUM(transaction_details.quantity), 0) as total_sold'),
                DB::raw('COALESCE(SUM(transaction_details.subtotal), 0) as total_revenue')
            )
                ->leftJoin('transaction_details', 'products.id', '=', 'transaction_details.product_id')
                ->groupBy('products.id')
                ->orderBy('total_sold', 'DESC')
                ->take(10)
                ->get();

            // Metode pembayaran populer
            $paymentMethods = Transaction::select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
                ->whereDate('created_at', today())
                ->groupBy('payment_method')
                ->get();

            $context = "=== LAPORAN PENJUALAN LENGKAP ===\n\n";

            $context .= "PERFORMA PENJUALAN:\n";
            $context .= "- Hari ini: {$todayTransactions} transaksi, Rp " . number_format($todayRevenue, 0, ',', '.') . "\n";
            $context .= "- Kemarin: Rp " . number_format($yesterdayRevenue, 0, ',', '.') . "\n";
            $context .= "- Minggu ini: Rp " . number_format($weekRevenue, 0, ',', '.') . "\n";
            $context .= "- Bulan ini: Rp " . number_format($monthRevenue, 0, ',', '.') . "\n";

            if ($yesterdayRevenue > 0) {
                $growth = (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100;
                $context .= "- Pertumbuhan vs kemarin: " . number_format($growth, 2) . "%\n";
            }
            $context .= "\n";

            $context .= "STATUS INVENTORI:\n";
            $context .= "- Total produk: {$totalProducts}\n";
            $context .= "- Total kategori: {$totalCategories}\n";
            $context .= "- Produk stok rendah (<10): {$lowStockProducts->count()}\n";
            $context .= "- Produk habis: {$outOfStockProducts->count()}\n\n";

            if ($lowStockProducts->count() > 0) {
                $context .= "PRODUK STOK RENDAH:\n";
                foreach ($lowStockProducts as $product) {
                    $context .= "- {$product->name}: {$product->stock} unit tersisa\n";
                }
                $context .= "\n";
            }

            if ($topProducts->count() > 0) {
                $context .= "TOP 10 PRODUK TERLARIS:\n";
                foreach ($topProducts as $index => $product) {
                    $context .= ($index + 1) . ". {$product->name}:\n";
                    $context .= "   - Terjual: {$product->total_sold} unit\n";
                    $context .= "   - Revenue: Rp " . number_format($product->total_revenue, 0, ',', '.') . "\n";
                    $context .= "   - Stok tersisa: {$product->stock} unit\n";
                }
                $context .= "\n";
            }

            if ($paymentMethods->count() > 0) {
                $context .= "METODE PEMBAYARAN HARI INI:\n";
                foreach ($paymentMethods as $method) {
                    $context .= "- {$method->payment_method}: {$method->count} transaksi, ";
                    $context .= "Rp " . number_format($method->total, 0, ',', '.') . "\n";
                }
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting sales context', ['error' => $e->getMessage()]);
            return "Data penjualan tidak dapat diakses saat ini.";
        }
    }

    // ... (method getProductContext dan getTransactionContext tetap sama)
    private function getProductContext()
    {
        try {
            $products = Product::with('category')->get();
            $lowStockProducts = Product::where('stock', '<', 10)->get();
            $outOfStockProducts = Product::where('stock', '=', 0)->get();
            $categories = Category::withCount('products')->get();

            // Produk dengan nilai stok tertinggi
            $highValueProducts = Product::select('*', DB::raw('price * stock as stock_value'))
                ->orderBy('stock_value', 'DESC')
                ->take(5)
                ->get();

            $context = "=== DATA PRODUK LENGKAP ===\n\n";

            $context .= "RINGKASAN INVENTORI:\n";
            $context .= "- Total produk: " . $products->count() . "\n";
            $context .= "- Total kategori: " . $categories->count() . "\n";
            $context .= "- Produk stok rendah: " . $lowStockProducts->count() . "\n";
            $context .= "- Produk habis: " . $outOfStockProducts->count() . "\n";
            $context .= "- Nilai total inventori: Rp " . number_format($products->sum(function ($p) {
                return $p->price * $p->stock;
            }), 0, ',', '.') . "\n\n";

            $context .= "KATEGORI PRODUK:\n";
            foreach ($categories as $category) {
                $categoryProducts = $products->where('category_id', $category->id);
                $categoryValue = $categoryProducts->sum(function ($p) {
                    return $p->price * $p->stock;
                });
                $context .= "- {$category->name}: {$category->products_count} produk, ";
                $context .= "Nilai: Rp " . number_format($categoryValue, 0, ',', '.') . "\n";
            }
            $context .= "\n";

            if ($highValueProducts->count() > 0) {
                $context .= "PRODUK NILAI STOK TERTINGGI:\n";
                foreach ($highValueProducts as $index => $product) {
                    $stockValue = $product->price * $product->stock;
                    $context .= ($index + 1) . ". {$product->name}:\n";
                    $context .= "   - Harga: Rp " . number_format($product->price, 0, ',', '.') . "\n";
                    $context .= "   - Stok: {$product->stock} unit\n";
                    $context .= "   - Nilai total: Rp " . number_format($stockValue, 0, ',', '.') . "\n";
                }
                $context .= "\n";
            }

            if ($lowStockProducts->count() > 0) {
                $context .= "⚠️ PERINGATAN STOK RENDAH:\n";
                foreach ($lowStockProducts as $product) {
                    $context .= "- {$product->name} ({$product->category->name}): {$product->stock} unit\n";
                }
                $context .= "\n";
            }

            if ($outOfStockProducts->count() > 0) {
                $context .= "🚫 PRODUK HABIS:\n";
                foreach ($outOfStockProducts as $product) {
                    $context .= "- {$product->name} ({$product->category->name})\n";
                }
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting product context', ['error' => $e->getMessage()]);
            return "Data produk tidak dapat diakses saat ini.";
        }
    }

    private function extractDate($message)
    {
        try {
            // Pattern untuk tanggal Indonesia
            $patterns = [
                // Format: 18 November 2025, 18 Nov 2025
                '/(\d{1,2})\s+(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember|jan|feb|mar|apr|mei|jun|jul|agt|sep|okt|nov|des)\s+(\d{4})/i',
                // Format: 18-11-2025, 18/11/2025
                '/(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})/',
                // Format: 2025-11-18
                '/(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/',
            ];

            $monthMap = [
                'januari' => '01',
                'februari' => '02',
                'maret' => '03',
                'april' => '04',
                'mei' => '05',
                'juni' => '06',
                'juli' => '07',
                'agustus' => '08',
                'september' => '09',
                'oktober' => '10',
                'november' => '11',
                'desember' => '12',
                'jan' => '01',
                'feb' => '02',
                'mar' => '03',
                'apr' => '04',
                'jun' => '06',
                'jul' => '07',
                'agt' => '08',
                'sep' => '09',
                'okt' => '10',
                'nov' => '11',
                'des' => '12'
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $message, $matches)) {
                    if (count($matches) === 4) {
                        // Format nama bulan
                        if (isset($monthMap[strtolower($matches[2])])) {
                            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                            $month = $monthMap[strtolower($matches[2])];
                            $year = $matches[3];
                            return \Carbon\Carbon::parse("$year-$month-$day");
                        }
                        // Format YYYY-MM-DD atau DD-MM-YYYY
                        if (strlen($matches[1]) === 4) {
                            // YYYY-MM-DD
                            return \Carbon\Carbon::parse($matches[1] . '-' . $matches[2] . '-' . $matches[3]);
                        } else {
                            // DD-MM-YYYY
                            return \Carbon\Carbon::parse($matches[3] . '-' . $matches[2] . '-' . $matches[1]);
                        }
                    }
                }
            }

            // Deteksi keyword relatif
            if (str_contains($message, 'kemarin')) {
                return \Carbon\Carbon::yesterday();
            }
            if (str_contains($message, 'besok')) {
                return \Carbon\Carbon::tomorrow();
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

            // Query data untuk tanggal tersebut
            $transactions = Transaction::whereDate('created_at', $dateForDb)->count();
            $revenue = Transaction::whereDate('created_at', $dateForDb)->sum('total');

            $profit = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.created_at', $dateForDb)
                ->sum('transaction_details.profit');

            $transactionDetails = Transaction::with(['user', 'details.product'])
                ->whereDate('created_at', $dateForDb)
                ->get();

            $context = "=== DATA UNTUK TANGGAL {$dateStr} ===\n\n";

            $context .= "⚠️ PENTING: Ini adalah data AKTUAL dari database untuk tanggal {$dateStr}\n\n";

            if ($transactions > 0) {
                $context .= "📊 RINGKASAN TANGGAL {$dateStr}:\n";
                $context .= "✅ Total Transaksi: {$transactions} transaksi\n";
                $context .= "✅ Total Pendapatan: Rp " . number_format($revenue, 0, ',', '.') . "\n";
                $context .= "✅ Total PROFIT: Rp " . number_format($profit, 0, ',', '.') . "\n";

                if ($revenue > 0) {
                    $profitMargin = ($profit / $revenue) * 100;
                    $context .= "✅ Margin Profit: " . number_format($profitMargin, 2) . "%\n\n";
                }

                // Detail transaksi
                $context .= "DETAIL TRANSAKSI:\n";
                foreach ($transactionDetails as $index => $transaction) {
                    $transactionProfit = $transaction->details->sum('profit');
                    $context .= "\nTransaksi #" . ($index + 1) . ":\n";
                    $context .= "  - Invoice: {$transaction->invoice_number}\n";
                    $context .= "  - Waktu: {$transaction->created_at->format('H:i:s')}\n";
                    $context .= "  - Kasir: {$transaction->user->name}\n";
                    $context .= "  - Metode: {$transaction->payment_method}\n";
                    $context .= "  - Total: Rp " . number_format($transaction->total, 0, ',', '.') . "\n";
                    $context .= "  - PROFIT: Rp " . number_format($transactionProfit, 0, ',', '.') . "\n";

                    $context .= "  - Produk:\n";
                    foreach ($transaction->details as $detail) {
                        $context .= "    * {$detail->product->name}: {$detail->quantity} x Rp " .
                            number_format($detail->price, 0, ',', '.') .
                            " (Profit: Rp " . number_format($detail->profit, 0, ',', '.') . ")\n";
                    }
                }
            } else {
                $context .= "❌ TIDAK ADA TRANSAKSI pada tanggal {$dateStr}\n";
                $context .= "Tanggal ini tidak memiliki data transaksi dalam database.\n";
            }

            $context .= "\n💡 Semua data di atas adalah data FINAL dari database.\n";

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting context by date', ['error' => $e->getMessage()]);
            return "Maaf, terjadi kesalahan saat mengambil data untuk tanggal tersebut.";
        }
    }

    private function getTransactionContext()
    {
        try {
            // Data statistik umum
            $todayTransactions = Transaction::whereDate('created_at', today())->count();
            $todayRevenue = Transaction::whereDate('created_at', today())->sum('total');
            $monthTransactions = Transaction::whereMonth('created_at', now()->month)->count();
            $monthRevenue = Transaction::whereMonth('created_at', now()->month)->sum('total');
            $totalRevenue = Transaction::sum('total');

            // Transaksi terbaru dengan detail lengkap
            $recentTransactions = Transaction::with(['user', 'details.product'])
                ->latest()
                ->take(10)
                ->get();

            // Transaksi hari ini dengan detail
            $todayTransactionsDetailed = Transaction::with(['user', 'details.product'])
                ->whereDate('created_at', today())
                ->get();

            $context = "=== DATA TRANSAKSI LENGKAP ===\n\n";

            $context .= "RINGKASAN STATISTIK:\n";
            $context .= "- Transaksi hari ini: {$todayTransactions} transaksi\n";
            $context .= "- Pendapatan hari ini: Rp " . number_format($todayRevenue, 0, ',', '.') . "\n";
            $context .= "- Transaksi bulan ini: {$monthTransactions} transaksi\n";
            $context .= "- Pendapatan bulan ini: Rp " . number_format($monthRevenue, 0, ',', '.') . "\n";
            $context .= "- Total pendapatan keseluruhan: Rp " . number_format($totalRevenue, 0, ',', '.') . "\n\n";

            // Detail transaksi hari ini
            if ($todayTransactionsDetailed->count() > 0) {
                $context .= "DETAIL TRANSAKSI HARI INI:\n";
                foreach ($todayTransactionsDetailed as $index => $transaction) {
                    $context .= "\nTransaksi #" . ($index + 1) . ":\n";
                    $context .= "  - Invoice: {$transaction->invoice_number}\n";
                    $context .= "  - Waktu: {$transaction->created_at->format('H:i:s')}\n";
                    $context .= "  - Kasir: {$transaction->user->name}\n";
                    $context .= "  - Metode Bayar: {$transaction->payment_method}\n";
                    $context .= "  - Subtotal: Rp " . number_format($transaction->subtotal, 0, ',', '.') . "\n";

                    if ($transaction->discount > 0) {
                        $context .= "  - Diskon: Rp " . number_format($transaction->discount, 0, ',', '.') . "\n";
                    }
                    if ($transaction->tax > 0) {
                        $context .= "  - Pajak: Rp " . number_format($transaction->tax, 0, ',', '.') . "\n";
                    }

                    $context .= "  - Total: Rp " . number_format($transaction->total, 0, ',', '.') . "\n";
                    $context .= "  - Dibayar: Rp " . number_format($transaction->paid, 0, ',', '.') . "\n";
                    $context .= "  - Kembalian: Rp " . number_format($transaction->change, 0, ',', '.') . "\n";

                    // Detail produk yang dibeli
                    $context .= "  - Produk yang dibeli:\n";
                    foreach ($transaction->details as $detail) {
                        $context .= "    * {$detail->product->name}: {$detail->quantity} x Rp " .
                            number_format($detail->price, 0, ',', '.') .
                            " = Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n";
                    }
                }
                $context .= "\n";
            }

            // 10 Transaksi terbaru untuk referensi
            $context .= "10 TRANSAKSI TERBARU:\n";
            foreach ($recentTransactions as $index => $transaction) {
                $itemCount = $transaction->details->sum('quantity');
                $context .= ($index + 1) . ". {$transaction->invoice_number} - ";
                $context .= "Rp " . number_format($transaction->total, 0, ',', '.') . " ";
                $context .= "({$itemCount} item, {$transaction->payment_method}) - ";
                $context .= "{$transaction->created_at->format('d/m/Y H:i')}\n";
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting transaction context', ['error' => $e->getMessage()]);
            return "Data transaksi tidak dapat diakses saat ini.";
        }
    }

    private function getProfitContext()
    {
        try {
            // Profit hari ini
            $todayProfit = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.created_at', today())
                ->sum('transaction_details.profit');

            $todayRevenue = Transaction::whereDate('created_at', today())->sum('total');
            $todayTransactions = Transaction::whereDate('created_at', today())->count();

            // Profit kemarin untuk perbandingan
            $yesterdayProfit = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.created_at', today()->subDay())
                ->sum('transaction_details.profit');

            // Profit minggu ini
            $weekProfit = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->where('transactions.created_at', '>=', now()->startOfWeek())
                ->sum('transaction_details.profit');

            // Profit bulan ini
            $monthProfit = DB::table('transaction_details')
                ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->whereMonth('transactions.created_at', now()->month)
                ->sum('transaction_details.profit');

            // Transaksi dengan profit tertinggi hari ini
            $topProfitTransactions = Transaction::with('details.product')
                ->whereDate('created_at', today())
                ->get()
                ->map(function ($transaction) {
                    $profit = $transaction->details->sum('profit');
                    return [
                        'invoice' => $transaction->invoice_number,
                        'profit' => $profit,
                        'total' => $transaction->total,
                        'items' => $transaction->details->count(),
                    ];
                })
                ->sortByDesc('profit')
                ->take(5);

            $context = "=== DATA PROFIT LENGKAP ===\n\n";

            $context .= "⚠️ PENTING: Semua data profit di bawah ini adalah DATA FINAL dari kolom 'profit' di database.\n";
            $context .= "JANGAN hitung ulang profit. GUNAKAN data ini langsung.\n\n";

            $context .= "📊 RINGKASAN PROFIT:\n";
            $context .= "✅ PROFIT HARI INI: Rp " . number_format($todayProfit, 0, ',', '.') . "\n";
            $context .= "   - Dari {$todayTransactions} transaksi\n";
            $context .= "   - Total penjualan: Rp " . number_format($todayRevenue, 0, ',', '.') . "\n";

            if ($yesterdayProfit > 0) {
                $profitGrowth = (($todayProfit - $yesterdayProfit) / $yesterdayProfit) * 100;
                $context .= "   - Pertumbuhan vs kemarin: " . number_format($profitGrowth, 2) . "%\n";
            }

            $profitMargin = $todayRevenue > 0 ? ($todayProfit / $todayRevenue) * 100 : 0;
            $context .= "   - Margin profit: " . number_format($profitMargin, 2) . "%\n\n";

            $context .= "✅ PROFIT KEMARIN: Rp " . number_format($yesterdayProfit, 0, ',', '.') . "\n";
            $context .= "✅ PROFIT MINGGU INI: Rp " . number_format($weekProfit, 0, ',', '.') . "\n";
            $context .= "✅ PROFIT BULAN INI: Rp " . number_format($monthProfit, 0, ',', '.') . "\n\n";

            if ($topProfitTransactions->count() > 0) {
                $context .= "🏆 TOP 5 TRANSAKSI PALING MENGUNTUNGKAN HARI INI:\n";
                foreach ($topProfitTransactions as $index => $trans) {
                    $context .= ($index + 1) . ". Invoice {$trans['invoice']}:\n";
                    $context .= "   - Profit: Rp " . number_format($trans['profit'], 0, ',', '.') . "\n";
                    $context .= "   - Total penjualan: Rp " . number_format($trans['total'], 0, ',', '.') . "\n";
                    $context .= "   - Jumlah item: {$trans['items']}\n";
                }
                $context .= "\n";
            }

            $context .= "💡 CATATAN:\n";
            $context .= "- Semua angka profit di atas adalah data FINAL dari database\n";
            $context .= "- Profit dihitung per item transaksi berdasarkan selisih harga jual dan HPP\n";
            $context .= "- Data ini dapat langsung digunakan untuk analisis bisnis\n";

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting profit context', ['error' => $e->getMessage()]);
            return "Data profit tidak dapat diakses saat ini.";
        }
    }

    public function render()
    {
        return view('livewire.ai-chat');
    }
}
