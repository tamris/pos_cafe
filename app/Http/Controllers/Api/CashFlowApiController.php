<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Models\CashierShift;
use App\Models\ExpenseCategory;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CashFlowApiController extends Controller
{
    /**
     * ==========================================
     * POS / CASHIER ENDPOINTS (SHIFT LEVEL 1)
     * ==========================================
     */

    /**
     * Ambil list kategori pengeluaran & kas masuk yang aktif untuk kasir di POS.
     * GET /api/pos/cash-flow/categories
     */
    public function getCategories(Request $request): JsonResponse
    {
        $query = ExpenseCategory::active()->whereNotIn('slug', ['gaji-bonus-karyawan', 'sewa-tempat-bangunan', 'bahan-baku-besar-supplier'])->orderBy('name', 'asc');

        if ($request->filled('type') && in_array($request->type, ['expense', 'cash_in'])) {
            if ($request->type === 'expense') {
                $query->forExpense();
            } else {
                $query->forCashIn();
            }
        }

        $categories = $query->get()->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'type' => $cat->type,
                'description' => $cat->description,
                'is_default' => (bool) $cat->is_default,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori berhasil dimuat.',
            'data' => $categories,
        ]);
    }

    /**
     * Ambil riwayat kas masuk & kas keluar pada shift aktif kasir saat ini.
     * GET /api/pos/cash-flow/current
     */
    public function getCurrentShiftMovements(Request $request): JsonResponse
    {
        $user = $request->user();

        $activeShift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('start_time')
            ->first();

        if (!$activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada shift kasir yang sedang aktif.',
                'data' => null,
            ], 404);
        }

        $activeShift->recalculateTotals();

        $movements = CashMovement::where('shift_id', $activeShift->id)
            ->where('source', 'drawer')
            ->latest()
            ->get()
            ->map(function ($m) {
                return $this->formatMovementData($m);
            });

        return response()->json([
            'success' => true,
            'message' => 'Data arus kas shift aktif berhasil dimuat.',
            'data' => [
                'shift_id' => $activeShift->id,
                'starting_cash' => (float) $activeShift->starting_cash,
                'cash_sales' => (float) $activeShift->cash_sales,
                'total_cash_in' => (float) $activeShift->total_cash_in,
                'total_cash_out' => (float) $activeShift->total_cash_out,
                'expected_cash' => (float) $activeShift->expected_cash,
                'movements_count' => $movements->count(),
                'movements' => $movements,
            ],
        ]);
    }

    /**
     * Kasir mencatat uang masuk (Pay In) atau uang keluar (Pay Out / Petty Cash) dari laci.
     * POST /api/pos/cash-flow
     */
    public function storeShiftMovement(Request $request): JsonResponse
    {
        $user = $request->user();

        // Kasir wajib memiliki shift yang sedang aktif
        $activeShift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('start_time')
            ->first();

        if (!$activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mencatat arus kas karena belum ada shift aktif yang dibuka.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:100',
            'category_id' => 'nullable|exists:expense_categories,id',
            'category_name' => 'nullable|string|max:100',
            'notes' => 'required|string|max:500',
            'receipt_image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'type.required' => 'Tipe arus kas harus ditentukan (in atau out).',
            'type.in' => 'Tipe arus kas hanya boleh in (Kas Masuk) atau out (Kas Keluar).',
            'amount.required' => 'Nominal uang tidak boleh kosong.',
            'amount.numeric' => 'Nominal uang harus berupa angka valid.',
            'amount.min' => 'Nominal minimal pencatatan adalah Rp 100.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'notes.required' => 'Keterangan/alasan pencatatan wajib diisi.',
            'receipt_image.image' => 'File bukti harus berupa gambar (JPEG, PNG, JPG, WEBP).',
            'receipt_image.max' => 'Ukuran foto bukti maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $type = $request->input('type');
        $amount = (float) $request->input('amount');
        $notes = trim($request->input('notes'));

        // Cek nama kategori
        $categoryName = 'Lain-lain';
        $categoryId = null;
        if ($request->filled('category_id')) {
            $cat = ExpenseCategory::find($request->input('category_id'));
            if ($cat) {
                $categoryId = $cat->id;
                $categoryName = $cat->name;
            }
        } elseif ($request->filled('category_name')) {
            $categoryName = trim($request->input('category_name'));
        }

        // Upload foto bukti jika ada
        $imagePath = null;
        if ($request->hasFile('receipt_image')) {
            $imagePath = $request->file('receipt_image')->store('receipts', 'public');
        }

        try {
            $movement = DB::transaction(function () use ($user, $activeShift, $categoryId, $categoryName, $type, $amount, $notes, $imagePath) {
                $m = CashMovement::create([
                    'user_id' => $user->id,
                    'shift_id' => $activeShift->id,
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'type' => $type,
                    'source' => 'drawer',
                    'amount' => $amount,
                    'notes' => $notes,
                    'receipt_image' => $imagePath,
                    'movement_date' => now(),
                ]);

                // Hitung ulang saldo laci kasir seketika
                $activeShift->recalculateTotals();

                return $m;
            });

            // Kirim notifikasi Telegram ke owner (Opsional / Background)
            try {
                app(TelegramService::class)->sendCashMovementNotification($movement);
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim notifikasi Telegram arus kas: ' . $e->getMessage());
            }

            $label = $type === 'in' ? 'Kas Masuk (Pay In)' : 'Kas Keluar (Pay Out)';
            $formattedNominal = number_format($amount, 0, ',', '.');

            return response()->json([
                'success' => true,
                'message' => "{$label} sebesar Rp {$formattedNominal} berhasil dicatat ke laci kasir.",
                'data' => [
                    'movement' => $this->formatMovementData($movement),
                    'shift_summary' => [
                        'shift_id' => $activeShift->id,
                        'total_cash_in' => (float) $activeShift->total_cash_in,
                        'total_cash_out' => (float) $activeShift->total_cash_out,
                        'expected_cash' => (float) $activeShift->expected_cash,
                    ],
                ],
                'receipt_payload' => $this->buildReceiptSlipPayload($movement),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error storing cashier cash movement: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan arus kas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ambil data slip bukti thermal 58mm untuk printer bluetooth.
     * GET /api/pos/cash-flow/{id}/receipt
     */
    public function getReceiptPayload($id, Request $request): JsonResponse
    {
        $movement = CashMovement::with(['user', 'shift', 'category'])->find($id);

        if (!$movement) {
            return response()->json([
                'success' => false,
                'message' => 'Data arus kas tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payload bukti arus kas siap dicetak.',
            'receipt_payload' => $this->buildReceiptSlipPayload($movement),
        ]);
    }

    /**
     * ==========================================
     * ADMIN & OWNER ENDPOINTS (LEVEL 1 & 2)
     * ==========================================
     */

    /**
     * List dan filter semua riwayat arus kas (Laci & Bank/Kas Besar).
     * GET /api/admin/cash-flow
     */
    public function index(Request $request): JsonResponse
    {
        $query = CashMovement::with(['user:id,name,email', 'category:id,name,type', 'shift:id,status,start_time,end_time'])
            ->latest('movement_date');

        // Filter tipe (in / out)
        if ($request->filled('type') && in_array($request->type, ['in', 'out'])) {
            $query->where('type', $request->type);
        }

        // Filter sumber dana (cash, drawer, bank, petty_cash)
        if ($request->filled('source')) {
            if ($request->source === 'cash') {
                $query->whereIn('source', ['drawer', 'petty_cash']);
            } elseif (in_array($request->source, ['drawer', 'bank', 'petty_cash'])) {
                $query->where('source', $request->source);
            }
        }

        // Filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter kasir / user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter shift tertentu
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }

        // Filter tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('movement_date', [$start, $end]);
        } elseif ($request->filled('date')) {
            $query->whereDate('movement_date', $request->date);
        }

        // Search text
        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('movement_number', 'like', $search)
                  ->orWhere('notes', 'like', $search)
                  ->orWhere('category_name', 'like', $search);
            });
        }

        $limit = min((int) $request->input('limit', 20), 100);
        $movements = $query->paginate($limit);

        $formattedItems = collect($movements->items())->map(function ($m) {
            return $this->formatMovementData($m);
        });

        return response()->json([
            'success' => true,
            'message' => 'Data riwayat arus kas berhasil dimuat.',
            'data' => $formattedItems,
            'meta' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
            ],
        ]);
    }

    /**
     * Catat pengeluaran toko Level 2 (Beban Toko dari Bank / Kas Utama) oleh Admin.
     * POST /api/admin/cash-flow
     */
    public function storeGeneralExpense(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:in,out',
            'source' => 'required|in:cash,drawer,bank,petty_cash',
            'amount' => 'required|numeric|min:100',
            'category_id' => 'nullable|exists:expense_categories,id',
            'category_name' => 'nullable|string|max:100',
            'notes' => 'required|string|max:500',
            'movement_date' => 'nullable|date',
            'shift_id' => 'nullable|exists:cashier_shifts,id',
            'receipt_image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'type.required' => 'Tipe transaksi kas harus dipilih.',
            'source.required' => 'Sumber dana (Laci, Rekening Bank, atau Kas Toko) harus dipilih.',
            'amount.required' => 'Nominal pengeluaran harus diisi.',
            'amount.numeric' => 'Nominal harus berupa angka valid.',
            'amount.min' => 'Nominal minimal Rp 100.',
            'notes.required' => 'Keterangan pengeluaran toko wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $source = $request->input('source');
        if ($source === 'cash') {
            $source = 'petty_cash';
        }
        $shiftId = $request->input('shift_id');

        // Jika sumber laci tapi shift_id kosong, coba ambil shift aktif saat ini jika ada
        if ($source === 'drawer' && empty($shiftId)) {
            $activeShift = CashierShift::where('status', 'open')->latest('start_time')->first();
            $shiftId = $activeShift?->id;
        }

        $categoryId = null;
        $categoryName = 'Pengeluaran Toko';
        if ($request->filled('category_id')) {
            $cat = ExpenseCategory::find($request->input('category_id'));
            if ($cat) {
                $categoryId = $cat->id;
                $categoryName = $cat->name;
            }
        } elseif ($request->filled('category_name')) {
            $categoryName = trim($request->input('category_name'));
        }

        $imagePath = null;
        if ($request->hasFile('receipt_image')) {
            $imagePath = $request->file('receipt_image')->store('receipts', 'public');
        }

        $movementDate = $request->filled('movement_date')
            ? Carbon::parse($request->input('movement_date'))
            : now();

        try {
            $movement = DB::transaction(function () use ($user, $shiftId, $categoryId, $categoryName, $source, $request, $imagePath, $movementDate) {
                $m = CashMovement::create([
                    'user_id' => $user->id,
                    'shift_id' => $shiftId,
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'type' => $request->input('type'),
                    'source' => $source,
                    'amount' => (float) $request->input('amount'),
                    'notes' => trim($request->input('notes')),
                    'receipt_image' => $imagePath,
                    'movement_date' => $movementDate,
                ]);

                // Jika dana bersumber dari laci kasir dan terhubung ke shift, update shift tersebut
                if ($source === 'drawer' && !empty($shiftId)) {
                    $shift = CashierShift::find($shiftId);
                    $shift?->recalculateTotals();
                }

                return $m;
            });

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran toko berhasil dicatat.',
                'data' => $this->formatMovementData($movement),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Admin storeGeneralExpense Error: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat pengeluaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detail satu transaksi arus kas.
     * GET /api/admin/cash-flow/{id}
     */
    public function show($id): JsonResponse
    {
        $movement = CashMovement::with(['user:id,name,email', 'category', 'shift'])->find($id);

        if (!$movement) {
            return response()->json([
                'success' => false,
                'message' => 'Data arus kas tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail arus kas berhasil dimuat.',
            'data' => $this->formatMovementData($movement),
        ]);
    }

    /**
     * Hapus (soft-delete) catatan arus kas.
     * DELETE /api/admin/cash-flow/{id}
     */
    public function destroy($id): JsonResponse
    {
        $movement = CashMovement::find($id);

        if (!$movement) {
            return response()->json([
                'success' => false,
                'message' => 'Data arus kas tidak ditemukan.',
            ], 404);
        }

        $shiftId = $movement->shift_id;
        $source = $movement->source;

        $movement->delete();

        // Jika terhubung ke laci kasir, hitung ulang saldo shift
        if ($source === 'drawer' && !empty($shiftId)) {
            $shift = CashierShift::find($shiftId);
            $shift?->recalculateTotals();
        }

        return response()->json([
            'success' => true,
            'message' => 'Catatan arus kas berhasil dihapus.',
        ]);
    }

    /**
     * Ringkasan Arus Kas & Analisis Beban Pengeluaran untuk Dashboard Owner.
     * GET /api/admin/cash-flow/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $period = $request->input('period', 'today'); // today, month, custom
        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();

        if ($period === 'month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        }

        // 1. Total Penjualan (Omzet) dari Transaksi Selesai
        $salesQuery = Transaction::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalSales = (float) $salesQuery->sum('total');
        $cashSales = (float) (clone $salesQuery)->whereRaw('LOWER(payment_method) = ?', ['cash'])->sum('total');
        $nonCashSales = (float) (clone $salesQuery)->whereRaw('LOWER(payment_method) != ?', ['cash'])->sum('total');

        // 2. Arus Kas (Movements)
        $movements = CashMovement::whereBetween('movement_date', [$startDate, $endDate])->get();

        // Kas Masuk (Pay In)
        $cashInTotal = (float) $movements->where('type', 'in')->sum('amount');
        $cashInDrawer = (float) $movements->where('type', 'in')->where('source', 'drawer')->sum('amount');
        $cashInBank = (float) $movements->where('type', 'in')->where('source', 'bank')->sum('amount');

        // Kas Keluar (Pay Out / Expense)
        $cashOutTotal = (float) $movements->where('type', 'out')->sum('amount');
        $cashOutDrawer = (float) $movements->where('type', 'out')->where('source', 'drawer')->sum('amount');
        $cashOutBank = (float) $movements->where('type', 'out')->where('source', 'bank')->sum('amount');
        $cashOutPetty = (float) $movements->where('type', 'out')->where('source', 'petty_cash')->sum('amount');

        // Net Cash Flow Toko = Total Pemasukan Penjualan + Kas Masuk - Total Pengeluaran
        $totalInflow = $totalSales + $cashInTotal;
        $netCashFlow = $totalInflow - $cashOutTotal;

        // 3. Saldo Kas Riil Toko (Real-Time / All-Time - Tidak terpengaruh filter tanggal)
        $allSalesQuery = Transaction::where('status', 'completed');
        $allSalesTotal = (float) $allSalesQuery->sum('total');
        $allCashSales = (float) (clone $allSalesQuery)->whereRaw('LOWER(payment_method) = ?', ['cash'])->sum('total');
        $allNonCashSales = (float) (clone $allSalesQuery)->whereRaw('LOWER(payment_method) != ?', ['cash'])->sum('total');

        $allMovements = CashMovement::all();
        $allCashInDrawer = (float) $allMovements->where('type', 'in')->where('source', 'drawer')->sum('amount');
        $allCashInBank = (float) $allMovements->where('type', 'in')->where('source', 'bank')->sum('amount');
        $allCashInTotal = (float) $allMovements->where('type', 'in')->sum('amount');

        $allCashOutDrawer = (float) $allMovements->where('type', 'out')->where('source', 'drawer')->sum('amount');
        $allCashOutPetty = (float) $allMovements->where('type', 'out')->where('source', 'petty_cash')->sum('amount');
        $allCashOutBank = (float) $allMovements->where('type', 'out')->where('source', 'bank')->sum('amount');
        $allCashOutTotal = (float) $allMovements->where('type', 'out')->sum('amount');

        $realCashBalance = ($allCashSales + $allCashInDrawer) - ($allCashOutDrawer + $allCashOutPetty);
        $realBankBalance = ($allNonCashSales + $allCashInBank) - $allCashOutBank;
        $totalRealBalance = ($allSalesTotal + $allCashInTotal) - $allCashOutTotal;

        // Breakdown Beban Pengeluaran per Kategori (untuk Chart / Analisis)
        $expenseMovements = $movements->where('type', 'out');
        $categoryBreakdown = $expenseMovements->groupBy('category_name')->map(function ($group, $name) use ($cashOutTotal) {
            $sum = (float) $group->sum('amount');
            $percentage = $cashOutTotal > 0 ? round(($sum / $cashOutTotal) * 100, 1) : 0;
            return [
                'category' => $name,
                'total_amount' => $sum,
                'count' => $group->count(),
                'percentage' => $percentage,
            ];
        })->values()->sortByDesc('total_amount')->values();

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan arus kas berhasil dimuat.',
            'data' => [
                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
                'sales' => [
                    'total_sales' => $totalSales,
                    'cash_sales' => $cashSales,
                    'non_cash_sales' => $nonCashSales,
                ],
                'cash_in' => [
                    'total' => $cashInTotal,
                    'total_inflow' => $totalInflow,
                    'drawer' => $cashInDrawer,
                    'bank' => $cashInBank,
                ],
                'total_balance' => [
                    'real_balance' => $totalRealBalance,
                    'cash_balance' => $realCashBalance,
                    'bank_balance' => $realBankBalance,
                ],
                'cash_out' => [
                    'total' => $cashOutTotal,
                    'drawer' => $cashOutDrawer,      // Pengeluaran Kasir Laci
                    'bank' => $cashOutBank,          // Pengeluaran Transfer Bank Toko
                    'petty_cash' => $cashOutPetty,   // Pengeluaran Kas Toko
                ],
                'net_cash_flow' => $netCashFlow,
                'category_breakdown' => $categoryBreakdown,
            ],
        ]);
    }

    /**
     * ==========================================
     * MASTER EXPENSE CATEGORIES (ADMIN CRUD)
     * ==========================================
     */

    /**
     * List semua kategori untuk Admin.
     * GET /api/admin/expense-categories
     */
    public function getCategoriesAdmin(): JsonResponse
    {
        $categories = ExpenseCategory::withCount('cashMovements')
            ->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Master kategori pengeluaran berhasil dimuat.',
            'data' => $categories,
        ]);
    }

    /**
     * Tambah kategori baru oleh Admin.
     * POST /api/admin/expense-categories
     */
    public function storeCategory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:expense_categories,name',
            'type' => 'required|in:expense,cash_in,both',
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah pernah dibuat.',
            'type.required' => 'Tipe kategori wajib dipilih.',
            'type.in' => 'Tipe kategori hanya boleh expense, cash_in, atau both.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $category = ExpenseCategory::create([
            'name' => trim($request->input('name')),
            'type' => $request->input('type'),
            'description' => $request->input('description'),
            'is_default' => false,
            'is_active' => true,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Kategori '{$category->name}' berhasil ditambahkan.",
            'data' => $category,
        ], 201);
    }

    /**
     * Update kategori oleh Admin.
     * PUT /api/admin/expense-categories/{id}
     */
    public function updateCategory(Request $request, $id): JsonResponse
    {
        $category = ExpenseCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:expense_categories,name,' . $id,
            'type' => 'required|in:expense,cash_in,both',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $category->update([
            'name' => trim($request->input('name')),
            'type' => $request->input('type'),
            'description' => $request->input('description'),
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : $category->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $category,
        ]);
    }

    /**
     * Hapus kategori custom oleh Admin (Kategori default sistem diproteksi).
     * DELETE /api/admin/expense-categories/{id}
     */
    public function deleteCategory($id): JsonResponse
    {
        $category = ExpenseCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        if ($category->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori bawaan sistem tidak dapat dihapus. Anda dapat menonaktifkannya jika tidak ingin digunakan.',
            ], 403);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }

    /**
     * ==========================================
     * HELPER METHODS
     * ==========================================
     */

    /**
     * Format response item CashMovement.
     */
    private function formatMovementData(CashMovement $movement): array
    {
        $typeLabel = $movement->type === 'in' ? 'Kas Masuk' : 'Kas Keluar';
        $sourceLabel = match ($movement->source) {
            'drawer' => 'Tunai (Laci)',
            'bank' => 'Non-Tunai (Bank)',
            'petty_cash' => 'Tunai (Kas Toko)',
            'cash' => 'Tunai',
            default => ucfirst($movement->source),
        };

        return [
            'id' => $movement->id,
            'movement_number' => $movement->movement_number,
            'type' => $movement->type,
            'type_label' => $typeLabel,
            'source' => $movement->source,
            'source_label' => $sourceLabel,
            'amount' => (float) $movement->amount,
            'amount_formatted' => 'Rp ' . number_format($movement->amount, 0, ',', '.'),
            'category_id' => $movement->category_id,
            'category_name' => $movement->category_name,
            'notes' => $movement->notes,
            'receipt_image' => $movement->receipt_image,
            'receipt_image_url' => $movement->receipt_image_url,
            'movement_date' => $movement->movement_date?->toIso8601String(),
            'movement_date_formatted' => $movement->movement_date?->translatedFormat('d M Y, H:i'),
            'user' => [
                'id' => $movement->user?->id,
                'name' => $movement->user?->name ?? 'User',
            ],
            'shift_id' => $movement->shift_id,
            'created_at' => $movement->created_at?->toIso8601String(),
        ];
    }

    /**
     * Build ESC/POS 58mm Thermal Receipt Payload untuk bukti pengeluaran / kas masuk kasir.
     */
    private function buildReceiptSlipPayload(CashMovement $movement): array
    {
        $setting = Setting::first();
        $isOut = $movement->type === 'out';

        return [
            'type' => 'CASH_MOVEMENT_SLIP',
            'paper_width' => 58,
            'header' => [
                'shop_name' => $setting->shop_name ?? 'POS Cafe',
                'title' => $isOut ? 'BUKTI KAS KELUAR' : 'BUKTI KAS MASUK',
            ],
            'movement_number' => $movement->movement_number,
            'date' => $movement->movement_date?->format('d/m/Y H:i'),
            'cashier_name' => $movement->user?->name ?? 'Kasir',
            'shift_id' => $movement->shift_id,
            'source' => $movement->source === 'drawer' ? 'Laci Kasir' : ucfirst($movement->source),
            'type' => $movement->type,
            'category' => $movement->category_name,
            'amount' => (float) $movement->amount,
            'amount_formatted' => 'Rp ' . number_format($movement->amount, 0, ',', '.'),
            'notes' => $movement->notes,
            'footer' => [
                'note' => 'Simpan slip ini bersama nota pembelian fisik sebagai arsip rekonsiliasi.',
            ],
        ];
    }
}
