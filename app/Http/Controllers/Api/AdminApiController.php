<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminApiController extends Controller
{
    /**
     * 1. Live Financial & Operations Dashboard for Owner / Admin.
     * GET /api/admin/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $dateParam = $request->query('date', now()->format('Y-m-d'));

        // Validate date format if custom date passed
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal tidak valid (gunakan format YYYY-MM-DD).',
            ], 422);
        }

        $dateStart = Carbon::parse($dateParam)->startOfDay();
        $dateEnd = Carbon::parse($dateParam)->endOfDay();

        // Base query for transactions created on selected date
        $transactionsToday = Transaction::whereBetween('created_at', [$dateStart, $dateEnd]);

        // Completed transactions (Actual Realized Revenue)
        $completedQuery = (clone $transactionsToday)->where('status', 'completed');
        $totalRevenue = (float) $completedQuery->sum('total');
        $totalTransactions = (int) $completedQuery->count();
        $avgPerTransaction = $totalTransactions > 0 ? round($totalRevenue / $totalTransactions, 2) : 0;

        // Items sold and Estimated Profit
        $completedTrxIds = (clone $completedQuery)->pluck('id');
        $totalItemsSold = (int) TransactionDetail::whereIn('transaction_id', $completedTrxIds)->sum('quantity');
        $rawProfit = (float) TransactionDetail::whereIn('transaction_id', $completedTrxIds)->sum('profit');
        $totalDiscount = (float) (clone $completedQuery)->sum('discount');
        $totalProfit = max(0.0, $rawProfit - $totalDiscount);
        $profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0.0;

        // Payment Method Breakdown
        $cashSales = (float) (clone $completedQuery)->where('payment_method', 'cash')->sum('total');
        $cashCount = (int) (clone $completedQuery)->where('payment_method', 'cash')->count();

        $qrisSales = (float) (clone $completedQuery)->where('payment_method', 'qris')->sum('total');
        $qrisCount = (int) (clone $completedQuery)->where('payment_method', 'qris')->count();

        $transferSales = (float) (clone $completedQuery)->where('payment_method', 'transfer')->sum('total');
        $transferCount = (int) (clone $completedQuery)->where('payment_method', 'transfer')->count();

        // Order Source Breakdown (Kasir POS vs Online Self-Order)
        $posSales = (float) (clone $completedQuery)
            ->where(function ($q) {
                $q->where('order_source', '!=', 'self_order')
                  ->orWhereNull('order_source');
            })->sum('total');
        $posCount = (int) (clone $completedQuery)
            ->where(function ($q) {
                $q->where('order_source', '!=', 'self_order')
                  ->orWhereNull('order_source');
            })->count();

        $onlineSales = (float) (clone $completedQuery)
            ->where('order_source', 'self_order')
            ->sum('total');
        $onlineCount = (int) (clone $completedQuery)
            ->where('order_source', 'self_order')
            ->count();

        // Order Type Breakdown (Dine-in vs Takeaway)
        $dineInSales = (float) (clone $completedQuery)->whereIn('order_type', ['dine_in', 'dinein'])->sum('total');
        $dineInCount = (int) (clone $completedQuery)->whereIn('order_type', ['dine_in', 'dinein'])->count();

        $takeawaySales = (float) (clone $completedQuery)->where(function ($q) {
            $q->whereIn('order_type', ['takeaway', 'take_away', 'bungkus', 'delivery'])
              ->orWhere(function ($sub) {
                  $sub->whereNotIn('order_type', ['dine_in', 'dinein'])
                      ->whereNotNull('order_type');
              });
        })->sum('total');
        $takeawayCount = (int) (clone $completedQuery)->where(function ($q) {
            $q->whereIn('order_type', ['takeaway', 'take_away', 'bungkus', 'delivery'])
              ->orWhere(function ($sub) {
                  $sub->whereNotIn('order_type', ['dine_in', 'dinein'])
                      ->whereNotNull('order_type');
              });
        })->count();

        // Cancellations Today
        $cancelledQuery = Transaction::whereBetween('cancelled_at', [$dateStart, $dateEnd])
            ->where('status', 'cancelled');
        $cancelledCount = (int) $cancelledQuery->count();
        $cancelledNominal = (float) $cancelledQuery->sum('total');

        // Current Active Cashier Shift (Realtime)
        $activeShift = CashierShift::with('user:id,name,email')
            ->where('status', 'open')
            ->latest('start_time')
            ->first();

        $activeShiftData = null;
        if ($activeShift) {
            $activeShift->recalculateTotals();
            $activeShiftData = [
                'shift_id' => $activeShift->id,
                'cashier' => [
                    'id' => $activeShift->user?->id,
                    'name' => $activeShift->user?->name ?? 'Kasir',
                ],
                'start_time' => $activeShift->start_time?->toIso8601String(),
                'starting_cash' => (float) $activeShift->starting_cash,
                'cash_sales' => (float) $activeShift->cash_sales,
                'expected_cash' => (float) $activeShift->expected_cash,
                'total_sales' => (float) $activeShift->total_sales,
                'total_transactions' => (int) $activeShift->total_transactions,
            ];
        }

        // Open Bills currently active / pending (Unpaid tables & active self-orders)
        $openBills = Transaction::where(function ($q) {
            $q->where('status', 'pending')
              ->orWhere(function ($sq) {
                  $sq->where('order_source', 'self_order')
                     ->whereIn('status', ['pending', 'processing', 'ready']);
              });
        });
        $openBillsList = $openBills->get();
        $openBillsCount = (int) $openBillsList->count();
        $openBillsPotentialTotal = (float) $openBillsList->filter(function ($b) {
            return $b->order_source !== 'self_order' || $b->payment_status !== 'paid';
        })->sum('total');

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil dimuat.',
            'data' => [
                'date' => $dateParam,
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'total_transactions' => $totalTransactions,
                    'average_per_transaction' => $avgPerTransaction,
                    'total_profit' => $totalProfit,
                    'profit_margin' => $profitMargin,
                    'total_items_sold' => $totalItemsSold,
                ],
                'payment_breakdown' => [
                    'cash' => [
                        'label' => 'Uang Tunai (Laci Kasir)',
                        'total' => $cashSales,
                        'count' => $cashCount,
                    ],
                    'qris' => [
                        'label' => 'QRIS Digital',
                        'total' => $qrisSales,
                        'count' => $qrisCount,
                    ],
                    'transfer' => [
                        'label' => 'Transfer Bank',
                        'total' => $transferSales,
                        'count' => $transferCount,
                    ],
                ],
                'order_source_breakdown' => [
                    'pos' => [
                        'label' => 'Kasir POS',
                        'total' => $posSales,
                        'count' => $posCount,
                    ],
                    'online_order' => [
                        'label' => 'Pesanan Online',
                        'total' => $onlineSales,
                        'count' => $onlineCount,
                    ],
                ],
                'order_type_breakdown' => [
                    'dine_in' => [
                        'label' => 'Dine-in (Makan di Tempat)',
                        'total' => $dineInSales,
                        'count' => $dineInCount,
                    ],
                    'takeaway' => [
                        'label' => 'Takeaway (Bungkus)',
                        'total' => $takeawaySales,
                        'count' => $takeawayCount,
                    ],
                ],
                'active_shift' => $activeShiftData,
                'open_bills_summary' => [
                    'count' => $openBillsCount,
                    'potential_revenue' => $openBillsPotentialTotal,
                ],
                'cancellations_summary' => [
                    'count' => $cancelledCount,
                    'total_nominal' => $cancelledNominal,
                ],
            ],
        ]);
    }

    /**
     * 2. Shift History & Z-Report Audit for Owner.
     * GET /api/admin/shifts/history
     */
    public function shiftHistory(Request $request): JsonResponse
    {
        $query = CashierShift::with('user:id,name,email')
            ->latest('start_time');

        // Optional filter by status ('open' or 'closed')
        if ($request->filled('status') && in_array($request->status, ['open', 'closed'])) {
            $query->where('status', $request->status);
        }

        // Optional filter by date
        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        }

        $limit = min((int) $request->input('limit', 20), 100);
        $shifts = $query->paginate($limit);

        $formattedData = collect($shifts->items())->map(function ($shift) {
            $diff = (float) ($shift->difference ?? 0);
            $discrepancyStatus = 'balanced';

            if ($shift->status === 'open') {
                $discrepancyStatus = 'in_progress';
            } elseif ($diff < 0) {
                $discrepancyStatus = 'shortage'; // Uang laci minus / kurang
            } elseif ($diff > 0) {
                $discrepancyStatus = 'overage';  // Uang laci lebih
            }

            return [
                'id' => $shift->id,
                'cashier' => [
                    'id' => $shift->user?->id,
                    'name' => $shift->user?->name ?? 'Kasir',
                    'email' => $shift->user?->email,
                ],
                'start_time' => $shift->start_time?->toIso8601String(),
                'end_time' => $shift->end_time?->toIso8601String(),
                'status' => $shift->status,
                'starting_cash' => (float) $shift->starting_cash,
                'cash_sales' => (float) $shift->cash_sales,
                'qris_sales' => (float) $shift->qris_sales,
                'transfer_sales' => (float) $shift->transfer_sales,
                'total_sales' => (float) $shift->total_sales,
                'total_transactions' => (int) $shift->total_transactions,
                'expected_cash' => (float) $shift->expected_cash,
                'actual_cash' => is_null($shift->actual_cash) ? null : (float) $shift->actual_cash,
                'difference' => is_null($shift->difference) ? null : (float) $shift->difference,
                'discrepancy_status' => $discrepancyStatus,
                'notes' => $shift->notes,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat shift kasir berhasil dimuat.',
            'data' => $formattedData,
            'meta' => [
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
                'per_page' => $shifts->perPage(),
                'total' => $shifts->total(),
            ],
        ]);
    }

    /**
     * 3. Shift Detail with transactions summary.
     * GET /api/admin/shifts/{id}
     */
    public function shiftDetail($id): JsonResponse
    {
        $shift = CashierShift::with([
            'user:id,name,email',
            'transactions' => function ($q) {
                $q->select('id', 'shift_id', 'invoice_number', 'total', 'payment_method', 'status', 'order_type', 'created_at')
                  ->latest();
            },
        ])->find($id);

        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'Data shift tidak ditemukan.',
            ], 404);
        }

        $diff = (float) ($shift->difference ?? 0);
        $discrepancyStatus = 'balanced';
        if ($shift->status === 'open') {
            $discrepancyStatus = 'in_progress';
        } elseif ($diff < 0) {
            $discrepancyStatus = 'shortage';
        } elseif ($diff > 0) {
            $discrepancyStatus = 'overage';
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail shift berhasil dimuat.',
            'data' => [
                'id' => $shift->id,
                'cashier' => [
                    'id' => $shift->user?->id,
                    'name' => $shift->user?->name ?? 'Kasir',
                    'email' => $shift->user?->email,
                ],
                'start_time' => $shift->start_time?->toIso8601String(),
                'end_time' => $shift->end_time?->toIso8601String(),
                'status' => $shift->status,
                'starting_cash' => (float) $shift->starting_cash,
                'cash_sales' => (float) $shift->cash_sales,
                'qris_sales' => (float) $shift->qris_sales,
                'transfer_sales' => (float) $shift->transfer_sales,
                'total_sales' => (float) $shift->total_sales,
                'total_transactions' => (int) $shift->total_transactions,
                'expected_cash' => (float) $shift->expected_cash,
                'actual_cash' => is_null($shift->actual_cash) ? null : (float) $shift->actual_cash,
                'difference' => is_null($shift->difference) ? null : (float) $shift->difference,
                'discrepancy_status' => $discrepancyStatus,
                'notes' => $shift->notes,
                'transactions' => $shift->transactions->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'invoice_number' => $t->invoice_number,
                        'total' => (float) $t->total,
                        'payment_method' => $t->payment_method,
                        'order_type' => $t->order_type,
                        'status' => $t->status,
                        'created_at' => $t->created_at?->toIso8601String(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * 4. Transactions List with filters for Owner.
     * GET /api/admin/transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $query = Transaction::with([
            'user:id,name',
            'cancelledBy:id,name',
            'shift:id,status,start_time,user_id',
            'shift.user:id,name',
            'details:id,transaction_id,quantity,price,subtotal,harga_beli,profit',
        ])->latest();

        // Filter status
        if ($request->filled('status') && in_array($request->status, ['completed', 'pending', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        // Filter date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter order source (pos or self_order)
        if ($request->filled('order_source')) {
            if ($request->order_source === 'self_order') {
                $query->where('order_source', 'self_order');
            } elseif ($request->order_source === 'pos') {
                $query->where(function ($q) {
                    $q->where('order_source', '!=', 'self_order')
                      ->orWhereNull('order_source');
                });
            }
        }

        // Keyword Search (Invoice, Customer, Table)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('table_number', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $transactions = $query->paginate($perPage);

        $formattedData = collect($transactions->items())->map(function ($t) {
            $isSelfOrder = ($t->order_source === 'self_order');
            $cashierName = $isSelfOrder
                ? 'Online (Self-Order)'
                : ($t->user?->name ?? ($t->shift?->user?->name ?? 'Kasir'));
            $cashierId = $isSelfOrder
                ? null
                : ($t->user?->id ?? $t->shift?->user?->id);

            return [
                'id' => $t->id,
                'invoice_number' => $t->invoice_number,
                'customer_name' => $t->customer_name ?? 'Pelanggan',
                'table_number' => $t->table_number,
                'order_type' => $t->order_type,
                'order_source' => $t->order_source ?? 'pos',
                'payment_method' => $t->payment_method,
                'payment_status' => $t->payment_status ?? ($t->status === 'completed' ? 'paid' : 'unpaid'),
                'subtotal' => (float) $t->subtotal,
                'discount' => (float) $t->discount,
                'tax' => (float) $t->tax,
                'total' => (float) $t->total,
                'profit' => $t->status === 'completed' ? max(0.0, (float) $t->details->sum('profit') - (float) ($t->discount ?? 0)) : 0.0,
                'paid' => (float) $t->paid,
                'change' => (float) $t->change,
                'status' => $t->status,
                'created_at' => $t->created_at?->toIso8601String(),
                'cashier_name' => $cashierName,
                'cashier' => [
                    'id' => $cashierId,
                    'name' => $cashierName,
                ],
                'shift' => $t->shift ? [
                    'id' => $t->shift->id,
                    'status' => $t->shift->status,
                ] : null,
                'cancelled_info' => $t->status === 'cancelled' ? [
                    'cancelled_at' => $t->cancelled_at?->toIso8601String(),
                    'cancelled_reason' => $t->cancelled_reason,
                    'cancelled_by_name' => $t->cancelledBy?->name ?? 'Admin',
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi berhasil dimuat.',
            'data' => $formattedData,
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * 5. Transaction Detail (Receipt & Line Items).
     * GET /api/admin/transactions/{id}
     */
    public function transactionDetail($id): JsonResponse
    {
        $transaction = Transaction::with([
            'user:id,name,email',
            'cancelledBy:id,name',
            'shift:id,status,start_time',
            'details.product:id,name,price,category_id',
            'details.product.category:id,name',
        ])->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        $items = $transaction->details->map(function ($detail) {
            return [
                'id' => $detail->id,
                'product_id' => $detail->product_id,
                'product_name' => $detail->product?->name ?? 'Item Dihapus',
                'category_name' => $detail->product?->category?->name ?? '-',
                'quantity' => (int) $detail->quantity,
                'price' => (float) $detail->price,
                'subtotal' => (float) $detail->subtotal,
                'notes' => $detail->notes,
                'addons' => $detail->addons ?? [],
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi berhasil dimuat.',
            'data' => [
                'id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'customer_name' => $transaction->customer_name ?? 'Pelanggan',
                'customer_phone' => $transaction->customer_phone,
                'table_number' => $transaction->table_number,
                'order_type' => $transaction->order_type,
                'order_source' => $transaction->order_source ?? 'pos',
                'payment_method' => $transaction->payment_method,
                'payment_status' => $transaction->payment_status,
                'subtotal' => (float) $transaction->subtotal,
                'discount' => (float) $transaction->discount,
                'tax' => (float) $transaction->tax,
                'total' => (float) $transaction->total,
                'paid' => (float) $transaction->paid,
                'change' => (float) $transaction->change,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at?->toIso8601String(),
                'cashier' => [
                    'id' => $transaction->user?->id,
                    'name' => $transaction->user?->name ?? 'Kasir',
                ],
                'shift' => $transaction->shift ? [
                    'id' => $transaction->shift->id,
                    'status' => $transaction->shift->status,
                ] : null,
                'cancelled_info' => $transaction->status === 'cancelled' ? [
                    'cancelled_at' => $transaction->cancelled_at?->toIso8601String(),
                    'cancelled_reason' => $transaction->cancelled_reason,
                    'cancelled_by' => [
                        'id' => $transaction->cancelledBy?->id,
                        'name' => $transaction->cancelledBy?->name ?? 'Admin',
                    ],
                ] : null,
                'items' => $items,
            ],
        ]);
    }

    /**
     * 6. Void / Cancel Transaction (Owner/Admin Exclusive Authority).
     * POST /api/admin/transactions/{id}/void
     */
    public function voidTransaction(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:3|max:255',
        ], [
            'reason.required' => 'Alasan pembatalan transaksi wajib diisi.',
            'reason.min' => 'Alasan pembatalan minimal 3 karakter.',
            'reason.max' => 'Alasan pembatalan maksimal 255 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $transaction = Transaction::with('shift')->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        // Check if already cancelled
        if ($transaction->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini sudah dibatalkan sebelumnya pada ' . optional($transaction->cancelled_at)->format('d M Y H:i') . '.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $adminUser = $request->user() ?? auth()->user();

            $transaction->status = 'cancelled';
            $transaction->cancelled_at = now();
            $transaction->cancelled_by = $adminUser ? $adminUser->id : null;
            $transaction->cancelled_reason = trim($request->input('reason'));
            $transaction->save();

            // Automatic & atomic shift recalculation
            if ($transaction->shift) {
                $transaction->shift->recalculateTotals();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Transaksi {$transaction->invoice_number} berhasil dibatalkan.",
                'data' => [
                    'id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'status' => $transaction->status,
                    'total' => (float) $transaction->total,
                    'cancelled_at' => $transaction->cancelled_at?->toIso8601String(),
                    'cancelled_reason' => $transaction->cancelled_reason,
                    'cancelled_by' => [
                        'id' => $adminUser->id,
                        'name' => $adminUser->name,
                    ],
                    'shift' => $transaction->shift ? [
                        'id' => $transaction->shift->id,
                        'total_sales' => (float) $transaction->shift->total_sales,
                        'expected_cash' => (float) $transaction->shift->expected_cash,
                        'difference' => (float) ($transaction->shift->difference ?? 0),
                    ] : null,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 7. Active Open Bills Monitoring for Owner.
     * GET /api/admin/open-bills
     */
    public function openBills(Request $request): JsonResponse
    {
        $openBills = Transaction::with(['user:id,name', 'details.product:id,name'])
            ->where(function ($q) {
                $q->where('status', 'pending')
                  ->orWhere(function ($sq) {
                      $sq->where('order_source', 'self_order')
                         ->whereIn('status', ['pending', 'processing', 'ready']);
                  });
            })
            ->latest()
            ->get();

        $formattedData = $openBills->map(function ($bill) {
            $elapsedMinutes = $bill->created_at ? max(0, (int) abs(now()->diffInMinutes($bill->created_at, false))) : 0;
            $rawTable = trim($bill->table_number ?? '');
            $tableDisplay = !empty($rawTable) ? $rawTable : '-';
            $cashierDisplay = $bill->order_source === 'self_order'
                ? 'Self-Order (QR)'
                : ($bill->user?->name ?? 'Kasir');

            $itemsSummary = $bill->details->take(2)->map(function ($d) {
                return ($d->quantity > 1 ? $d->quantity . 'x ' : '') . ($d->product?->name ?? 'Menu');
            })->implode(', ');
            if ($bill->details->count() > 2) {
                $itemsSummary .= ' +' . ($bill->details->count() - 2) . ' lainnya';
            }

            return [
                'id' => $bill->id,
                'invoice_number' => $bill->invoice_number,
                'table_number' => $tableDisplay,
                'customer_name' => $bill->customer_name ?? 'Tamu Meja',
                'order_type' => $bill->order_type,
                'order_source' => $bill->order_source ?? 'pos',
                'status' => $bill->status,
                'payment_status' => $bill->payment_status ?? 'unpaid',
                'total' => (float) $bill->total,
                'items_count' => (int) $bill->details->sum('quantity'),
                'items_summary' => $itemsSummary ?: null,
                'formatted_time' => $bill->created_at ? $bill->created_at->format('H:i') : '-',
                'created_at' => $bill->created_at?->toIso8601String(),
                'elapsed_minutes' => $elapsedMinutes,
                'cashier_name' => $cashierDisplay,
            ];
        });

        $unpaidBillsTotal = $openBills->filter(function ($b) {
            return $b->payment_status !== 'paid';
        })->sum('total');

        return response()->json([
            'success' => true,
            'message' => 'Daftar tagihan meja aktif (Open Bills) berhasil dimuat.',
            'data' => $formattedData,
            'total_active' => $formattedData->count(),
            'total_amount' => (float) $unpaidBillsTotal,
        ]);
    }
}
