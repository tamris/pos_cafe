<?php

namespace App\Http\Controllers;

use App\Services\GeminiAIService;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeminiAIController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiAIService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = $request->input('message');

        Log::info('AI Chat Request', [
            'message' => $message,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name
        ]);

        $context = '';

        // Deteksi jenis pertanyaan dan siapkan konteks
        if ($this->isSalesRelated($message)) {
            Log::info('Detected sales-related question');
            $context = $this->getSalesContext();
        } elseif ($this->isProductRelated($message)) {
            Log::info('Detected product-related question');
            $context = $this->getProductContext();
        } elseif ($this->isTransactionRelated($message)) {
            Log::info('Detected transaction-related question');
            $context = $this->getTransactionContext();
        } else {
            Log::info('Detected general question');
        }

        Log::info('Context prepared', ['context_length' => strlen($context)]);

        $response = $this->geminiService->generateResponse($message, $context);

        Log::info('AI Chat Response', [
            'response_length' => strlen($response),
            'user_message' => $message
        ]);

        return response()->json([
            'response' => $response,
            'timestamp' => now()->format('H:i')
        ]);
    }

    private function isSalesRelated($message)
    {
        $keywords = ['penjualan', 'jual', 'laporan', 'pendapatan', 'revenue', 'omzet', 'terjual', 'transaksi', 'hasil', 'kinerja'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isProductRelated($message)
    {
        $keywords = ['produk', 'barang', 'stok', 'inventory', 'kategori', 'item', 'produk terlaris', 'barang apa', 'apa produk'];
        return $this->containsKeywords($message, $keywords);
    }

    private function isTransactionRelated($message)
    {
        $keywords = ['transaksi', 'pembelian', 'invoice', 'struk', 'pembayaran', 'beli', 'transaksi hari'];
        return $this->containsKeywords($message, $keywords);
    }

    private function containsKeywords($message, $keywords)
    {
        $message = strtolower($message);
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
            $todayTransactions = Transaction::whereDate('created_at', today())->count();
            $todayRevenue = Transaction::whereDate('created_at', today())->sum('total');
            $lowStockProducts = Product::where('stock', '<', 10)->count();
            $totalProducts = Product::count();
            $totalCategories = Category::count();

            $topProducts = Product::select('products.*', DB::raw('COALESCE(SUM(transaction_details.quantity), 0) as total_sold'))
                ->leftJoin('transaction_details', 'products.id', '=', 'transaction_details.product_id')
                ->groupBy('products.id')
                ->orderBy('total_sold', 'DESC')
                ->take(5)
                ->get();

            $context = "Data Penjualan:\n";
            $context .= "- Total transaksi hari ini: {$todayTransactions}\n";
            $context .= "- Pendapatan hari ini: Rp " . number_format($todayRevenue, 0, ',', '.') . "\n";
            $context .= "- Produk dengan stok rendah: {$lowStockProducts}\n";
            $context .= "- Total produk: {$totalProducts}\n";
            $context .= "- Total kategori: {$totalCategories}\n";

            if ($topProducts->count() > 0) {
                $context .= "\nProduk Terlaris:\n";
                foreach ($topProducts as $index => $product) {
                    $context .= ($index + 1) . ". {$product->name}: {$product->total_sold} terjual\n";
                }
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting sales context', ['error' => $e->getMessage()]);
            return "Data penjualan tidak dapat diakses saat ini.";
        }
    }

    private function getProductContext()
    {
        try {
            $products = Product::with('category')->get();
            $lowStockProducts = Product::where('stock', '<', 10)->get();
            $categories = Category::withCount('products')->get();

            $context = "Data Produk:\n";
            $context .= "Total produk: " . $products->count() . "\n";
            $context .= "Total kategori: " . $categories->count() . "\n";
            $context .= "Produk stok rendah: " . $lowStockProducts->count() . "\n\n";

            $context .= "Kategori:\n";
            foreach ($categories as $category) {
                $context .= "- {$category->name}: {$category->products_count} produk\n";
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting product context', ['error' => $e->getMessage()]);
            return "Data produk tidak dapat diakses saat ini.";
        }
    }

    private function getTransactionContext()
    {
        try {
            $todayTransactions = Transaction::whereDate('created_at', today())->count();
            $monthTransactions = Transaction::whereMonth('created_at', now()->month)->count();
            $totalRevenue = Transaction::sum('total');
            $recentTransactions = Transaction::with('user')->latest()->take(5)->get();

            $context = "Data Transaksi:\n";
            $context .= "Transaksi hari ini: {$todayTransactions}\n";
            $context .= "Transaksi bulan ini: {$monthTransactions}\n";
            $context .= "Total pendapatan: Rp " . number_format($totalRevenue, 0, ',', '.') . "\n\n";

            $context .= "Transaksi Terbaru:\n";
            foreach ($recentTransactions as $transaction) {
                $context .= "- {$transaction->invoice_number}: Rp " . number_format($transaction->total, 0, ',', '.') . " ({$transaction->payment_method})\n";
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting transaction context', ['error' => $e->getMessage()]);
            return "Data transaksi tidak dapat diakses saat ini.";
        }
    }
}