<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuSalesApiController extends Controller
{
    /**
     * 1. Daftar Penjualan Menu (Menu Sales Report)
     * GET /api/menu-sales
     * GET /api/pos/menu-sales
     * GET /api/admin/menu-sales
     */
    public function index(Request $request): JsonResponse
    {
        $period = $this->resolveDateRange($request);
        if ($period['error']) {
            return response()->json([
                'success' => false,
                'message' => $period['error'],
            ], 422);
        }

        $startDate = $period['start_date'];
        $endDate = $period['end_date'];

        // Base query: Transactions must be completed OR paid self-orders, excluding cancelled
        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        $this->applyValidTransactionScope($query);

        // Filter Category
        if ($request->filled('category_id') && is_numeric($request->input('category_id'))) {
            $query->where('products.category_id', (int) $request->input('category_id'));
        }

        // Filter Search Keyword (Product Name or SKU)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }

        // Filter Order Source ('pos', 'self_order', 'all')
        if ($request->filled('order_source') && $request->input('order_source') !== 'all') {
            $source = strtolower(trim($request->input('order_source')));
            if ($source === 'self_order' || $source === 'online') {
                $query->where('transactions.order_source', 'self_order');
            } elseif ($source === 'pos' || $source === 'kasir') {
                $query->where(function ($q) {
                    $q->whereNull('transactions.order_source')
                      ->orWhere('transactions.order_source', '!=', 'self_order');
                });
            }
        }

        // Aggregate per product
        $aggregatedQuery = (clone $query)
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'products.price as unit_price',
                'products.harga_beli as cost_price',
                'products.image',
                'products.is_active',
                'products.deleted_at',
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(transaction_details.quantity) as quantity_sold'),
                DB::raw('SUM(transaction_details.subtotal) as total_revenue'),
                DB::raw('SUM(COALESCE(transaction_details.harga_beli, products.harga_beli, 0) * transaction_details.quantity) as total_cost'),
                DB::raw('SUM(transaction_details.profit) as total_profit'),
                DB::raw('COUNT(DISTINCT transactions.id) as transactions_count')
            )
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.harga_beli',
                'products.image',
                'products.is_active',
                'products.deleted_at',
                'categories.id',
                'categories.name'
            );

        // Sorting
        $sortBy = strtolower($request->input('sort_by', 'quantity'));
        $sortDir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        switch ($sortBy) {
            case 'revenue':
            case 'total_revenue':
            case 'omset':
                $aggregatedQuery->orderBy('total_revenue', $sortDir)->orderBy('quantity_sold', $sortDir);
                break;
            case 'profit':
            case 'total_profit':
            case 'laba':
                $aggregatedQuery->orderBy('total_profit', $sortDir)->orderBy('quantity_sold', $sortDir);
                break;
            case 'name':
            case 'nama':
                $aggregatedQuery->orderBy('products.name', $sortDir);
                break;
            case 'quantity':
            case 'quantity_sold':
            case 'qty':
            case 'terlaris':
            default:
                $aggregatedQuery->orderBy('quantity_sold', $sortDir)->orderBy('total_revenue', $sortDir);
                break;
        }

        $allSoldItems = $aggregatedQuery->get();

        // Calculate global summary
        $totalQuantitySold = (int) $allSoldItems->sum('quantity_sold');
        $totalRevenue = (float) $allSoldItems->sum('total_revenue');
        $totalProfit = (float) $allSoldItems->sum('total_profit');
        $totalCost = max(0.0, (float) $allSoldItems->sum('total_cost'));
        $profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0.0;
        $totalUniqueItemsSold = $allSoldItems->count();

        $topProductItem = $allSoldItems->sortByDesc('quantity_sold')->first();
        $topSellingProduct = $topProductItem ? [
            'id' => (int) $topProductItem->product_id,
            'name' => $topProductItem->product_name,
            'quantity_sold' => (int) $topProductItem->quantity_sold,
            'total_revenue' => (float) $topProductItem->total_revenue,
        ] : null;

        // Map items with ranking, percentages, and full image URL
        $rankCounter = 1;
        $formattedItems = $allSoldItems->map(function ($item) use (&$rankCounter, $totalQuantitySold, $totalRevenue) {
            $itemQty = (int) $item->quantity_sold;
            $itemRevenue = (float) $item->total_revenue;
            $itemProfit = (float) $item->total_profit;
            $itemCost = max(0.0, (float) ($item->total_cost ?? ($itemRevenue - $itemProfit)));
            $itemMargin = $itemRevenue > 0 ? round(($itemProfit / $itemRevenue) * 100, 1) : 0.0;

            $salesShare = $totalQuantitySold > 0 ? round(($itemQty / $totalQuantitySold) * 100, 1) : 0.0;
            $revenueShare = $totalRevenue > 0 ? round(($itemRevenue / $totalRevenue) * 100, 1) : 0.0;

            return [
                'rank' => $rankCounter++,
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'category' => [
                    'id' => $item->category_id ? (int) $item->category_id : null,
                    'name' => $item->category_name ?? 'Tanpa Kategori',
                ],
                'image_url' => $this->getImageUrl($item->image),
                'is_active' => (bool) $item->is_active,
                'is_deleted' => !is_null($item->deleted_at),
                'unit_price' => (float) $item->unit_price,
                'cost_price' => (float) $item->cost_price,
                'quantity_sold' => $itemQty,
                'total_revenue' => $itemRevenue,
                'total_cost' => $itemCost,
                'total_profit' => $itemProfit,
                'profit_margin' => $itemMargin,
                'sales_share_percentage' => $salesShare,
                'revenue_share_percentage' => $revenueShare,
                'transactions_count' => (int) $item->transactions_count,
            ];
        });

        // Pagination / All items support
        $perPageInput = $request->input('per_page', 20);
        $isAll = in_array(strtolower((string) $perPageInput), ['all', '-1', '0']);
        $totalItems = $formattedItems->count();

        if ($isAll) {
            $pagedData = $formattedItems->values()->all();
            $meta = [
                'total' => $totalItems,
                'per_page' => $totalItems,
                'current_page' => 1,
                'last_page' => 1,
            ];
        } else {
            $perPage = max(1, min((int) $perPageInput, 100));
            $currentPage = max(1, (int) $request->input('page', 1));
            $pagedData = $formattedItems->forPage($currentPage, $perPage)->values()->all();
            $lastPage = (int) ceil($totalItems / $perPage);
            $meta = [
                'total' => $totalItems,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => max(1, $lastPage),
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Data penjualan menu berhasil dimuat.',
            'summary' => [
                'total_quantity_sold' => $totalQuantitySold,
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'profit_margin' => $profitMargin,
                'total_unique_items_sold' => $totalUniqueItemsSold,
                'top_selling_product' => $topSellingProduct,
            ],
            'period' => [
                'range' => $period['range'],
                'label' => $period['label'],
                'start_date' => $period['start_date']->toIso8601String(),
                'end_date' => $period['end_date']->toIso8601String(),
            ],
            'data' => $pagedData,
            'meta' => $meta,
        ]);
    }

    /**
     * 2. Top Best Sellers Ranking (Cocok untuk Widget / Dashboard Apps)
     * GET /api/menu-sales/top
     */
    public function topSelling(Request $request): JsonResponse
    {
        $period = $this->resolveDateRange($request);
        if ($period['error']) {
            return response()->json([
                'success' => false,
                'message' => $period['error'],
            ], 422);
        }

        $limit = max(1, min((int) $request->input('limit', 5), 50));

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$period['start_date'], $period['end_date']]);

        $this->applyValidTransactionScope($query);

        $topItems = $query->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'products.price as unit_price',
                'products.image',
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(transaction_details.quantity) as quantity_sold'),
                DB::raw('SUM(transaction_details.subtotal) as total_revenue'),
                DB::raw('SUM(transaction_details.profit) as total_profit')
            )
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.image',
                'categories.id',
                'categories.name'
            )
            ->orderByDesc('quantity_sold')
            ->orderByDesc('total_revenue')
            ->take($limit)
            ->get();

        $rank = 1;
        $formatted = $topItems->map(function ($item) use (&$rank) {
            $revenue = (float) $item->total_revenue;
            $profit = (float) $item->total_profit;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0.0;

            return [
                'rank' => $rank++,
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'category_name' => $item->category_name ?? 'Tanpa Kategori',
                'image_url' => $this->getImageUrl($item->image),
                'unit_price' => (float) $item->unit_price,
                'quantity_sold' => (int) $item->quantity_sold,
                'total_revenue' => $revenue,
                'total_profit' => $profit,
                'profit_margin' => $margin,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Top produk terlaris berhasil dimuat.',
            'period' => [
                'range' => $period['range'],
                'label' => $period['label'],
                'start_date' => $period['start_date']->toIso8601String(),
                'end_date' => $period['end_date']->toIso8601String(),
            ],
            'data' => $formatted,
        ]);
    }

    /**
     * 3. Penjualan Berdasarkan Kategori (Pie Chart / Donut Chart Apps)
     * GET /api/menu-sales/categories
     */
    public function categorySales(Request $request): JsonResponse
    {
        $period = $this->resolveDateRange($request);
        if ($period['error']) {
            return response()->json([
                'success' => false,
                'message' => $period['error'],
            ], 422);
        }

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$period['start_date'], $period['end_date']]);

        $this->applyValidTransactionScope($query);

        $categories = $query->select(
                'categories.id as category_id',
                DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name'),
                DB::raw('SUM(transaction_details.quantity) as quantity_sold'),
                DB::raw('SUM(transaction_details.subtotal) as total_revenue'),
                DB::raw('SUM(transaction_details.profit) as total_profit'),
                DB::raw('COUNT(DISTINCT products.id) as unique_products_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('quantity_sold')
            ->orderByDesc('total_revenue')
            ->get();

        $totalAllQty = (int) $categories->sum('quantity_sold');
        $totalAllRevenue = (float) $categories->sum('total_revenue');

        $formatted = $categories->map(function ($cat) use ($totalAllQty, $totalAllRevenue) {
            $qty = (int) $cat->quantity_sold;
            $revenue = (float) $cat->total_revenue;
            $profit = (float) $cat->total_profit;

            return [
                'category_id' => $cat->category_id ? (int) $cat->category_id : null,
                'category_name' => $cat->category_name,
                'unique_products_count' => (int) $cat->unique_products_count,
                'quantity_sold' => $qty,
                'total_revenue' => $revenue,
                'total_profit' => $profit,
                'quantity_share_percentage' => $totalAllQty > 0 ? round(($qty / $totalAllQty) * 100, 1) : 0.0,
                'revenue_share_percentage' => $totalAllRevenue > 0 ? round(($revenue / $totalAllRevenue) * 100, 1) : 0.0,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data penjualan kategori berhasil dimuat.',
            'summary' => [
                'total_quantity_sold' => $totalAllQty,
                'total_revenue' => $totalAllRevenue,
            ],
            'period' => [
                'range' => $period['range'],
                'label' => $period['label'],
                'start_date' => $period['start_date']->toIso8601String(),
                'end_date' => $period['end_date']->toIso8601String(),
            ],
            'data' => $formatted,
        ]);
    }

    /**
     * 4. Detail Drill-Down Penjualan Satu Menu (Tren Harian + Addons Favorit + Log Pesanan)
     * GET /api/menu-sales/{id}
     */
    public function detail(Request $request, $id): JsonResponse
    {
        $product = Product::with('category')->withTrashed()->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Menu / Produk tidak ditemukan.',
            ], 404);
        }

        $period = $this->resolveDateRange($request);
        if ($period['error']) {
            return response()->json([
                'success' => false,
                'message' => $period['error'],
            ], 422);
        }

        $startDate = $period['start_date'];
        $endDate = $period['end_date'];

        // Aggregate product sales in period
        $statsQuery = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transaction_details.product_id', $product->id)
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        $this->applyValidTransactionScope($statsQuery);

        $stats = $statsQuery->select(
                DB::raw('COALESCE(SUM(transaction_details.quantity), 0) as total_sold'),
                DB::raw('COALESCE(SUM(transaction_details.subtotal), 0) as total_revenue'),
                DB::raw('COALESCE(SUM(transaction_details.profit), 0) as total_profit'),
                DB::raw('COUNT(DISTINCT transactions.id) as transactions_count')
            )
            ->first();

        $totalSold = (int) ($stats->total_sold ?? 0);
        $totalRevenue = (float) ($stats->total_revenue ?? 0);
        $totalProfit = (float) ($stats->total_profit ?? 0);
        $transactionsCount = (int) ($stats->transactions_count ?? 0);
        $profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0.0;

        // Daily Trend for Line / Bar Charts in Apps
        $dailyTrendQuery = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transaction_details.product_id', $product->id)
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        $this->applyValidTransactionScope($dailyTrendQuery);

        $dailyTrend = $dailyTrendQuery->select(
                DB::raw('DATE(transactions.created_at) as date'),
                DB::raw('SUM(transaction_details.quantity) as quantity'),
                DB::raw('SUM(transaction_details.subtotal) as revenue'),
                DB::raw('SUM(transaction_details.profit) as profit')
            )
            ->groupBy(DB::raw('DATE(transactions.created_at)'))
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($day) {
                return [
                    'date' => $day->date,
                    'date_formatted' => Carbon::parse($day->date)->translatedFormat('d M Y'),
                    'quantity' => (int) $day->quantity,
                    'revenue' => (float) $day->revenue,
                    'profit' => (float) $day->profit,
                ];
            });

        // Popular Addons for this Menu
        $detailsWithAddons = TransactionDetail::where('product_id', $product->id)
            ->whereNotNull('addons')
            ->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                $this->applyValidTransactionScope($q)
                     ->whereBetween('transactions.created_at', [$startDate, $endDate]);
            })
            ->get();

        $addonStats = [];
        foreach ($detailsWithAddons as $det) {
            $addonList = is_array($det->addons)
                ? $det->addons
                : (is_string($det->addons) ? json_decode($det->addons, true) : []);

            if (is_array($addonList)) {
                foreach ($addonList as $addonItem) {
                    if (!is_array($addonItem)) {
                        continue;
                    }
                    $name = $addonItem['name'] ?? 'Add-on';
                    $price = (float) ($addonItem['price'] ?? 0);
                    $qty = (int) $det->quantity;

                    if (!isset($addonStats[$name])) {
                        $addonStats[$name] = [
                            'name' => $name,
                            'count' => 0,
                            'total_revenue' => 0.0,
                        ];
                    }
                    $addonStats[$name]['count'] += $qty;
                    $addonStats[$name]['total_revenue'] += ($price * $qty);
                }
            }
        }
        usort($addonStats, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        // 10 Recent Order Logs for this Menu in this period
        $recentDetails = TransactionDetail::with(['transaction' => function ($q) {
                $q->select('id', 'invoice_number', 'table_number', 'customer_name', 'order_type', 'order_source', 'payment_method', 'created_at');
            }])
            ->where('product_id', $product->id)
            ->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                $this->applyValidTransactionScope($q)
                     ->whereBetween('transactions.created_at', [$startDate, $endDate]);
            })
            ->latest('created_at')
            ->take(10)
            ->get();

        $recentOrders = $recentDetails->map(function ($d) {
            return [
                'transaction_id' => $d->transaction_id,
                'invoice_number' => $d->transaction?->invoice_number ?? '-',
                'customer_name' => $d->transaction?->customer_name ?? 'Pelanggan',
                'table_number' => $d->transaction?->table_number ?? '-',
                'order_type' => $d->transaction?->order_type ?? 'dine_in',
                'order_source' => $d->transaction?->order_source ?? 'pos',
                'payment_method' => $d->transaction?->payment_method ?? 'cash',
                'quantity' => (int) $d->quantity,
                'price' => (float) $d->price,
                'subtotal' => (float) $d->subtotal,
                'addons' => $d->addons ?? [],
                'created_at' => $d->transaction?->created_at?->toIso8601String(),
                'time_formatted' => $d->transaction?->created_at ? $d->transaction->created_at->format('d M Y H:i') : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Detail performa penjualan menu berhasil dimuat.',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'description' => $product->description,
                'barcode' => $product->barcode,
                'unit_price' => (float) $product->price,
                'cost_price' => (float) $product->harga_beli,
                'category' => [
                    'id' => $product->category?->id,
                    'name' => $product->category?->name ?? 'Tanpa Kategori',
                ],
                'image_url' => $this->getImageUrl($product->image),
                'is_active' => (bool) $product->is_active,
                'is_deleted' => $product->trashed(),
            ],
            'sales_summary' => [
                'quantity_sold' => $totalSold,
                'total_revenue' => $totalRevenue,
                'total_profit' => $totalProfit,
                'profit_margin' => $profitMargin,
                'transactions_count' => $transactionsCount,
            ],
            'daily_trend' => $dailyTrend,
            'popular_addons' => array_values($addonStats),
            'recent_orders' => $recentOrders,
            'period' => [
                'range' => $period['range'],
                'label' => $period['label'],
                'start_date' => $period['start_date']->toIso8601String(),
                'end_date' => $period['end_date']->toIso8601String(),
            ],
        ]);
    }

    /**
     * Helper: Menentukan scope transaksi yang sah (completed atau online paid, tanpa cancelled)
     */
    protected function applyValidTransactionScope($query)
    {
        return $query->where(function ($q) {
            $q->where('transactions.status', 'completed')
              ->orWhere(function ($sq) {
                  $sq->where('transactions.order_source', 'self_order')
                     ->where('transactions.payment_status', 'paid')
                     ->where('transactions.status', '!=', 'cancelled');
              });
        });
    }

    /**
     * Helper: Normalisasi URL gambar produk
     */
    protected function getImageUrl(?string $imagePath): ?string
    {
        if (empty($imagePath)) {
            return null;
        }

        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
            return $imagePath;
        }

        return asset('storage/' . ltrim($imagePath, '/'));
    }

    /**
     * Helper: Menentukan rentang tanggal dari request query (presets vs custom dates)
     */
    private function resolveDateRange(Request $request): array
    {
        // 1. Cek jika custom date diberikan
        if ($request->filled('start_date') || $request->filled('end_date') || $request->filled('from') || $request->filled('to')) {
            $fromStr = $request->input('start_date') ?? $request->input('from');
            $toStr = $request->input('end_date') ?? $request->input('to') ?? $fromStr;

            if ($fromStr && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromStr)) {
                return ['error' => 'Format start_date tidak valid (gunakan format YYYY-MM-DD).'];
            }
            if ($toStr && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $toStr)) {
                return ['error' => 'Format end_date tidak valid (gunakan format YYYY-MM-DD).'];
            }

            $startDate = Carbon::parse($fromStr)->startOfDay();
            $endDate = Carbon::parse($toStr)->endOfDay();

            // Auto-swap jika tanggal terbalik
            if ($startDate->gt($endDate)) {
                $temp = $startDate;
                $startDate = $endDate->copy()->startOfDay();
                $endDate = $temp->copy()->endOfDay();
            }

            return [
                'error' => null,
                'range' => 'custom',
                'label' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
        }

        // 2. Preset range
        $range = strtolower($request->input('range', 'this_month'));

        switch ($range) {
            case 'today':
            case 'hari_ini':
                $start = Carbon::today()->startOfDay();
                $end = Carbon::today()->endOfDay();
                $label = 'Hari Ini (' . $start->format('d M Y') . ')';
                break;

            case 'yesterday':
            case 'kemarin':
                $start = Carbon::yesterday()->startOfDay();
                $end = Carbon::yesterday()->endOfDay();
                $label = 'Kemarin (' . $start->format('d M Y') . ')';
                break;

            case 'this_week':
            case 'minggu_ini':
                $start = Carbon::now()->startOfWeek()->startOfDay();
                $end = Carbon::now()->endOfWeek()->endOfDay();
                $label = 'Minggu Ini (' . $start->format('d M') . ' - ' . $end->format('d M Y') . ')';
                break;

            case 'last_week':
            case 'minggu_lalu':
                $start = Carbon::now()->subWeek()->startOfWeek()->startOfDay();
                $end = Carbon::now()->subWeek()->endOfWeek()->endOfDay();
                $label = 'Minggu Lalu (' . $start->format('d M') . ' - ' . $end->format('d M Y') . ')';
                break;

            case 'last_month':
            case 'bulan_lalu':
                $start = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
                $end = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                $label = 'Bulan Lalu (' . $start->format('F Y') . ')';
                break;

            case 'this_year':
            case 'tahun_ini':
                $start = Carbon::now()->startOfYear()->startOfDay();
                $end = Carbon::now()->endOfYear()->endOfDay();
                $label = 'Tahun Ini (' . $start->format('Y') . ')';
                break;

            case 'all':
            case 'semua':
                $start = Carbon::parse('2020-01-01')->startOfDay();
                $end = Carbon::now()->endOfDay();
                $label = 'Semua Waktu';
                break;

            case 'this_month':
            case 'bulan_ini':
            default:
                $range = 'this_month';
                $start = Carbon::now()->startOfMonth()->startOfDay();
                $end = Carbon::now()->endOfMonth()->endOfDay();
                $label = 'Bulan Ini (' . $start->format('F Y') . ')';
                break;
        }

        return [
            'error' => null,
            'range' => $range,
            'label' => $label,
            'start_date' => $start,
            'end_date' => $end,
        ];
    }
}
