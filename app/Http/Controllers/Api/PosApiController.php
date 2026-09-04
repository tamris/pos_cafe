<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\CashierShift;
use App\Models\Setting;
use App\Models\Addon;
use App\Services\ReceiptPrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PosApiController extends Controller
{
    /**
     * Bootstrap all initial POS Data (Categories, Products, Tables, Settings, Active Shift).
     */
    public function bootstrap(Request $request)
    {
        $user = $request->user();

        // 1. Categories
        $categories = Category::where('is_active', true)
            ->select('id', 'name', 'created_at')
            ->orderBy('name', 'asc')
            ->get();

        // 2. Active Addons
        $addons = Addon::where('is_active', true)
            ->with(['categories:id,name'])
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => (float) $addon->price,
                    'harga_beli' => (float) ($addon->harga_beli ?? 0),
                    'category_ids' => $addon->categories->pluck('id')->toArray(),
                    'category_names' => $addon->categories->pluck('name')->toArray(),
                    'is_active' => (bool) $addon->is_active,
                ];
            });

        // 3. Active Products
        $products = Product::query()
            ->whereHas('category', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['category:id,name', 'category.addons'])
            ->withSum('transactionDetails as total_sold', 'quantity')
            ->orderByDesc('total_sold')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($p) {
                $imageUrl = null;
                if ($p->image) {
                    $imageUrl = Str::startsWith($p->image, 'http')
                        ? $p->image
                        : asset('storage/' . $p->image);
                }

                $availableAddons = $p->available_addons->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'name' => $a->name,
                        'price' => (float) $a->price,
                        'harga_beli' => (float) ($a->harga_beli ?? 0),
                    ];
                })->values()->toArray();

                return [
                    'id' => $p->id,
                    'category_id' => $p->category_id,
                    'category_name' => $p->category?->name ?? 'Uncategorized',
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'barcode' => $p->barcode,
                    'price' => (float) $p->price,
                    'harga_beli' => (float) ($p->harga_beli ?? 0),
                    'description' => $p->description,
                    'image_url' => $imageUrl,
                    'is_active' => (bool) $p->is_active,
                    'total_sold' => (int) ($p->total_sold ?? 0),
                    'available_addons' => $availableAddons,
                ];
            });

        // 3. Occupied Tables & Open Bills Count
        $openBillsQuery = Transaction::where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('order_source')
                  ->orWhere('order_source', '!=', 'self_order');
            });
        $openBillsCount = (clone $openBillsQuery)->count();
        $occupiedTables = $openBillsQuery
            ->whereNotNull('table_number')
            ->where('table_number', '!=', '')
            ->pluck('table_number')
            ->unique()
            ->values()
            ->toArray();

        // 4. Cafe Settings
        $setting = Setting::first();
        $shopLogoUrl = null;
        if ($setting && $setting->shop_logo) {
            $shopLogoUrl = Str::startsWith($setting->shop_logo, 'http')
                ? $setting->shop_logo
                : asset('storage/' . $setting->shop_logo);
        }

        $cafeSettings = [
            'shop_name' => $setting->shop_name ?? 'Cafe POS',
            'address' => $setting->address ?? 'Alamat Toko',
            'phone' => $setting->phone ?? '',
            'shop_logo_url' => $shopLogoUrl,
            'show_logo_receipt' => (bool) ($setting->show_logo_receipt ?? true),
            'receipt_footer' => $setting->receipt_footer ?? 'Terima Kasih Atas Kunjungannya!',
            'auto_print_receipt' => (bool) ($setting->auto_print_receipt ?? true),
            'auto_print_kitchen' => (bool) ($setting->auto_print_kitchen ?? false),
            'wifi_name' => $setting->wifi_name ?? '',
            'wifi_password' => $setting->wifi_password ?? '',
            'printer_paper_width' => 58, // 58mm thermal standard
        ];

        // 5. Active Shift
        $activeShift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($activeShift) {
            $activeShift->recalculateTotals();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ],
                'categories' => $categories,
                'products' => $products,
                'addons' => $addons,
                'occupied_tables' => $occupiedTables,
                'open_bills_count' => $openBillsCount,
                'settings' => $cafeSettings,
                'has_active_shift' => !is_null($activeShift),
                'active_shift' => $activeShift ? $this->formatShiftData($activeShift) : null,
                'order_types' => [
                    ['key' => 'dine_in', 'label' => 'Dine In (Meja)'],
                    ['key' => 'take_away', 'label' => 'Take Away'],
                    ['key' => 'delivery', 'label' => 'Delivery'],
                ],
                'payment_methods' => [
                    ['key' => 'cash', 'label' => 'Tunai (Cash)'],
                    ['key' => 'qris', 'label' => 'QRIS'],
                    ['key' => 'transfer', 'label' => 'Transfer Bank'],
                    ['key' => 'debit', 'label' => 'Kartu Debit / EDC'],
                ],
                'quick_cash_presets' => [10000, 20000, 50000, 100000, 200000],
                'server_time' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get All Active Add-ons & Toppings for POS.
     */
    public function getAddons(Request $request)
    {
        $addons = Addon::where('is_active', true)
            ->with(['categories:id,name'])
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => (float) $addon->price,
                    'harga_beli' => (float) ($addon->harga_beli ?? 0),
                    'category_ids' => $addon->categories->pluck('id')->toArray(),
                    'category_names' => $addon->categories->pluck('name')->toArray(),
                    'is_active' => (bool) $addon->is_active,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $addons,
        ]);
    }

    /**
     * Get Current Shift details.
     */
    public function currentShift(Request $request)
    {
        $user = $request->user();
        $shift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$shift) {
            return response()->json([
                'success' => true,
                'has_active_shift' => false,
                'data' => null,
            ]);
        }

        $shift->recalculateTotals();

        return response()->json([
            'success' => true,
            'has_active_shift' => true,
            'data' => $this->formatShiftData($shift),
        ]);
    }

    /**
     * Start Cashier Shift.
     */
    public function startShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'starting_cash' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Modal awal tidak boleh kosong atau negatif.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Check if there is already an open shift
        $existingShift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki shift aktif yang belum ditutup.',
                'data' => $this->formatShiftData($existingShift),
            ], 400);
        }

        $startingCash = (float) $request->input('starting_cash');

        $shift = CashierShift::create([
            'user_id' => $user->id,
            'start_time' => now(),
            'starting_cash' => $startingCash,
            'expected_cash' => $startingCash,
            'cash_sales' => 0,
            'qris_sales' => 0,
            'transfer_sales' => 0,
            'total_sales' => 0,
            'total_transactions' => 0,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift kasir berhasil dibuka dengan modal Rp ' . number_format($startingCash, 0, ',', '.'),
            'data' => $this->formatShiftData($shift),
        ]);
    }

    /**
     * End / Close Cashier Shift.
     */
    public function endShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Uang fisik riil tidak boleh kosong atau negatif.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $shift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ditemukan shift aktif untuk ditutup.',
            ], 404);
        }

        // GUARD: Cek apakah masih ada Open Bill yang belum selesai pada shift ini
        $pendingBillsCount = Transaction::where('status', 'pending')
            ->where('shift_id', $shift->id)
            ->where(function ($q) {
                $q->whereNull('order_source')
                  ->orWhere('order_source', '!=', 'self_order');
            })
            ->count();
        if ($pendingBillsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat menutup shift! Masih ada {$pendingBillsCount} Bill Aktif (Open Bill) pada shift ini yang belum diselesaikan.",
                'pending_count' => $pendingBillsCount,
            ], 400);
        }

        $shift->recalculateTotals();

        $actualCash = (float) $request->input('actual_cash');
        $expectedCash = (float) $shift->expected_cash;
        $difference = $actualCash - $expectedCash;
        $notes = $request->input('notes', '');

        $shift->update([
            'end_time' => now(),
            'actual_cash' => $actualCash,
            'difference' => $difference,
            'notes' => $notes,
            'status' => 'closed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift kasir berhasil ditutup.',
            'data' => $this->formatShiftData($shift),
            'receipt_payload' => $this->buildShiftReportReceipt($shift),
        ]);
    }

    /**
     * Checkout Sale Transaction (Dine In / Take Away / Delivery).
     */
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_type' => 'required|in:dine_in,take_away,delivery',
            'table_number' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:100',
            'payment_method' => 'required|in:cash,qris,transfer,debit',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'paid' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
            'items.*.addons' => 'nullable|array',
            'items.*.addons.*.id' => 'required_with:items.*.addons|integer',
            'items.*.addons.*.name' => 'required_with:items.*.addons|string|max:100',
            'items.*.addons.*.price' => 'required_with:items.*.addons|numeric|min:0',
            'open_bill_id' => 'nullable|integer|exists:transactions,id',
            'offline_id' => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Cashier shift check (optional for admin, required for cashier)
        $activeShift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (in_array($user->role, ['kasir', 'cashier']) && !$activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift kasir belum dibuka. Silakan buka shift terlebih dahulu.',
                'require_shift' => true,
            ], 403);
        }

        DB::beginTransaction();
        try {
            $orderType = $request->input('order_type', 'dine_in');
            $tableNumber = $orderType === 'dine_in' ? $request->input('table_number') : null;
            $customerName = $request->input('customer_name');
            $paymentMethod = $request->input('payment_method', 'cash');
            $discountPercent = (float) $request->input('discount_percent', 0);
            $taxPercent = (float) $request->input('tax_percent', 0);
            $paid = (float) $request->input('paid');
            $items = $request->input('items', []);
            $openBillId = $request->input('open_bill_id');

            // Calculate Subtotal from items
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += ((float) $item['price'] * (int) $item['quantity']);
            }

            $discountAmount = ($subtotal * $discountPercent) / 100;
            $taxAmount = ($subtotal * $taxPercent) / 100;
            $total = (float) $subtotal - $discountAmount + $taxAmount;

            if ($paid < $total && $paymentMethod === 'cash') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah uang yang dibayarkan kurang dari total tagihan (Rp ' . number_format($total, 0, ',', '.') . ').',
                ], 422);
            }

            $change = $paymentMethod === 'cash' ? max(0, $paid - $total) : 0;
            $shiftId = $activeShift?->id;

            if ($openBillId) {
                // Update existing Open Bill to Completed
                $transaction = Transaction::find($openBillId);
                if (!$transaction || $transaction->status !== 'pending') {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Bill pesanan tidak ditemukan atau sudah selesai dibayar.',
                    ], 404);
                }

                $transaction->update([
                    'shift_id' => $shiftId,
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'tax' => $taxAmount,
                    'total' => $total,
                    'paid' => $paid,
                    'change' => $change,
                    'payment_method' => $paymentMethod,
                    'order_type' => $orderType,
                    'table_number' => $tableNumber,
                    'customer_name' => $customerName,
                    'status' => 'completed',
                ]);

                $transaction->details()->delete();
            } else {
                // Create brand new transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'shift_id' => $shiftId,
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'tax' => $taxAmount,
                    'total' => $total,
                    'paid' => $paid,
                    'change' => $change,
                    'payment_method' => $paymentMethod,
                    'order_type' => $orderType,
                    'table_number' => $tableNumber,
                    'customer_name' => $customerName,
                    'status' => 'completed',
                ]);
            }

            // Insert details & compute profit
            foreach ($items as $item) {
                $product = Product::find($item['id']);
                $hargaBeli = $product ? (float) ($product->harga_beli ?? 0) : 0;
                $itemPrice = (float) $item['price'];
                $itemQty = (int) $item['quantity'];
                $itemSubtotal = $itemPrice * $itemQty;

                $totalAddonCost = 0;
                if (!empty($item['addons'])) {
                    $addonIds = array_column($item['addons'], 'id');
                    $totalAddonCost = (float) Addon::whereIn('id', $addonIds)->sum('harga_beli');
                }
                $unitCost = $hargaBeli + $totalAddonCost;
                $itemProfit = ($itemPrice - $unitCost) * $itemQty;

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['id'],
                    'quantity' => $itemQty,
                    'price' => $itemPrice,
                    'harga_beli' => $unitCost,
                    'subtotal' => $itemSubtotal,
                    'profit' => $itemProfit,
                    'notes' => $item['notes'] ?? null,
                    'addons' => !empty($item['addons']) ? $item['addons'] : null,
                ]);
            }

            DB::commit();

            if ($activeShift) {
                $activeShift->recalculateTotals();
            }

            $freshTransaction = Transaction::with(['details.product', 'user', 'shift'])->find($transaction->id);
            $receiptPayload = $this->buildCustomerReceiptPayload($freshTransaction);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses.',
                'data' => [
                    'transaction_id' => $freshTransaction->id,
                    'invoice_number' => $freshTransaction->invoice_number,
                    'total' => (float) $freshTransaction->total,
                    'paid' => (float) $freshTransaction->paid,
                    'change' => (float) $freshTransaction->change,
                    'payment_method' => $freshTransaction->payment_method,
                    'created_at' => $freshTransaction->created_at->format('Y-m-d H:i:s'),
                ],
                'receipt_payload' => $receiptPayload,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Open Bills list.
     */
    public function getOpenBills(Request $request)
    {
        $search = $request->input('search');

        $query = Transaction::with(['details.product', 'user'])
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('order_source')
                  ->orWhere('order_source', '!=', 'self_order');
            })
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('table_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $openBills = $query->get()->map(function ($bill) {
            return [
                'id' => $bill->id,
                'invoice_number' => $bill->invoice_number,
                'table_number' => $bill->table_number,
                'customer_name' => $bill->customer_name,
                'total' => (float) $bill->total,
                'subtotal' => (float) $bill->subtotal,
                'items_count' => $bill->details->sum('quantity'),
                'created_at' => $bill->created_at->format('H:i • d M Y'),
                'details' => $bill->details->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'product_id' => $d->product_id,
                        'name' => $d->product?->name ?? 'Menu',
                        'quantity' => (int) $d->quantity,
                        'price' => (float) $d->price,
                        'subtotal' => (float) $d->subtotal,
                        'notes' => $d->notes,
                        'addons' => $d->addons ?? [],
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $openBills,
        ]);
    }

    /**
     * Save / Hold Current Order as Open Bill.
     */
    public function saveOpenBill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_type' => 'required|in:dine_in,take_away',
            'table_number' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:100',
            'discount_percent' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
            'items.*.addons' => 'nullable|array',
            'items.*.addons.*.id' => 'required_with:items.*.addons|integer',
            'items.*.addons.*.name' => 'required_with:items.*.addons|string|max:100',
            'items.*.addons.*.price' => 'required_with:items.*.addons|numeric|min:0',
            'open_bill_id' => 'nullable|integer|exists:transactions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data Open Bill tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $tableNumber = $request->input('table_number');
        $customerName = $request->input('customer_name');

        if (empty($tableNumber) && empty($customerName)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih Nomor Meja atau isi Nama Pelanggan untuk Simpan Bill.',
            ], 422);
        }

        $activeShift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        DB::beginTransaction();
        try {
            $discountPercent = (float) $request->input('discount_percent', 0);
            $taxPercent = (float) $request->input('tax_percent', 0);
            $items = $request->input('items', []);
            $openBillId = $request->input('open_bill_id');

            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += ((float) $item['price'] * (int) $item['quantity']);
            }

            $discountAmount = ($subtotal * $discountPercent) / 100;
            $taxAmount = ($subtotal * $taxPercent) / 100;
            $total = (float) $subtotal - $discountAmount + $taxAmount;
            $shiftId = $activeShift?->id;

            if ($openBillId) {
                $transaction = Transaction::find($openBillId);
                if (!$transaction || $transaction->status !== 'pending') {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Bill tidak ditemukan atau sudah selesai.',
                    ], 404);
                }

                $transaction->update([
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'tax' => $taxAmount,
                    'total' => $total,
                    'order_type' => $request->input('order_type', 'dine_in'),
                    'table_number' => $tableNumber,
                    'customer_name' => $customerName,
                ]);

                $transaction->details()->delete();
            } else {
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'shift_id' => $shiftId,
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'tax' => $taxAmount,
                    'total' => $total,
                    'paid' => 0,
                    'change' => 0,
                    'payment_method' => 'cash',
                    'order_type' => $request->input('order_type', 'dine_in'),
                    'table_number' => $tableNumber,
                    'customer_name' => $customerName,
                    'status' => 'pending',
                ]);
            }

            foreach ($items as $item) {
                $product = Product::find($item['id']);
                $hargaBeli = $product ? (float) ($product->harga_beli ?? 0) : 0;
                $itemPrice = (float) $item['price'];
                $itemQty = (int) $item['quantity'];

                $totalAddonCost = 0;
                if (!empty($item['addons'])) {
                    $addonIds = array_column($item['addons'], 'id');
                    $totalAddonCost = (float) Addon::whereIn('id', $addonIds)->sum('harga_beli');
                }
                $unitCost = $hargaBeli + $totalAddonCost;

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['id'],
                    'quantity' => $itemQty,
                    'price' => $itemPrice,
                    'harga_beli' => $unitCost,
                    'subtotal' => $itemPrice * $itemQty,
                    'profit' => ($itemPrice - $unitCost) * $itemQty,
                    'notes' => $item['notes'] ?? null,
                    'addons' => !empty($item['addons']) ? $item['addons'] : null,
                ]);
            }

            DB::commit();

            $identifier = $transaction->table_number ? 'Meja ' . $transaction->table_number : ($transaction->customer_name ?: $transaction->invoice_number);

            return response()->json([
                'success' => true,
                'message' => "Pesanan {$identifier} berhasil disimpan (Open Bill).",
                'data' => [
                    'id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'table_number' => $transaction->table_number,
                    'customer_name' => $transaction->customer_name,
                    'total' => (float) $transaction->total,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan Open Bill: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific Open Bill detail for resuming into cart.
     */
    public function getOpenBillDetail($id)
    {
        $transaction = Transaction::with(['details.product'])->find($id);

        if (!$transaction || $transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Open Bill tidak ditemukan atau sudah selesai dibayar.',
            ], 404);
        }

        $items = $transaction->details->map(function ($d) {
            return [
                'id' => $d->id,
                'product_id' => $d->product_id,
                'name' => $d->product?->name ?? 'Menu',
                'price' => (float) $d->price,
                'harga_beli' => (float) $d->harga_beli,
                'quantity' => (int) $d->quantity,
                'subtotal' => (float) $d->subtotal,
                'notes' => $d->notes ?? '',
                'addons' => $d->addons ?? [],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'order_type' => $transaction->order_type,
                'table_number' => $transaction->table_number,
                'customer_name' => $transaction->customer_name,
                'subtotal' => (float) $transaction->subtotal,
                'discount' => (float) $transaction->discount,
                'tax' => (float) $transaction->tax,
                'total' => (float) $transaction->total,
                'items' => $items,
            ],
        ]);
    }

    /**
     * Cancel / Void Open Bill.
     */
    public function cancelOpenBill(Request $request, $id)
    {
        $user = $request->user();
        $transaction = Transaction::find($id);

        if (!$transaction || $transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Open Bill tidak ditemukan.',
            ], 404);
        }

        $transaction->update([
            'status' => 'cancelled',
            'cancelled_reason' => 'Dibatalkan Kasir via Mobile POS (Void Open Bill)',
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Bill {$transaction->invoice_number} berhasil dibatalkan.",
        ]);
    }

    /**
     * Today's Transactions History for Current Cashier.
     */
    public function todayTransactions(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');
        $status = $request->input('status', 'completed'); // 'completed', 'pending', 'cancelled', 'all'

        // 1. Stats Counter untuk filter tabs riwayat transaksi hari ini
        $baseTodayQuery = Transaction::whereDate('created_at', today());
        if ($user && in_array($user->role, ['kasir', 'cashier'])) {
            $baseTodayQuery->where('user_id', $user->id);
        }

        $statsCompleted = (clone $baseTodayQuery)->where('status', 'completed')->count();
        $statsPending = (clone $baseTodayQuery)->where('status', 'pending')->count();
        $statsCancelled = (clone $baseTodayQuery)->where('status', 'cancelled')->count();
        $statsAll = (clone $baseTodayQuery)->count();

        $query = Transaction::with(['details.product', 'user'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc');

        if ($user && in_array($user->role, ['kasir', 'cashier'])) {
            $query->where('user_id', $user->id);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('table_number', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(20)->through(function ($t) {
            return [
                'id' => $t->id,
                'invoice_number' => $t->invoice_number,
                'status' => $t->status,
                'order_type' => $t->order_type,
                'table_number' => $t->table_number,
                'customer_name' => $t->customer_name,
                'payment_method' => $t->payment_method,
                'total' => (float) $t->total,
                'paid' => (float) $t->paid,
                'change' => (float) $t->change,
                'items_count' => $t->details->sum('quantity'),
                'time' => $t->created_at->format('H:i:s'),
                'date' => $t->created_at->format('d M Y'),
                'details' => $t->details->map(function ($d) {
                    return [
                        'name' => $d->product?->name ?? 'Menu',
                        'quantity' => (int) $d->quantity,
                        'price' => (float) $d->price,
                        'subtotal' => (float) $d->subtotal,
                        'notes' => $d->notes,
                        'addons' => $d->addons ?? [],
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'stats' => [
                'all' => $statsAll,
                'completed' => $statsCompleted,
                'pending' => $statsPending,
                'cancelled' => $statsCancelled,
            ],
            'data' => $transactions,
        ]);
    }

    /**
     * Get Receipt Data formatted for 58mm Thermal Printer.
     */
    public function getReceiptData($id)
    {
        $transaction = Transaction::with(['details.product', 'user', 'shift'])->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->buildCustomerReceiptPayload($transaction),
        ]);
    }

    /**
     * Sync Batch Offline Transactions.
     */
    public function syncOffline(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transactions' => 'required|array|min:1',
            'transactions.*.offline_id' => 'required|string',
            'transactions.*.order_type' => 'required|in:dine_in,take_away,delivery',
            'transactions.*.payment_method' => 'required|in:cash,qris,transfer,debit',
            'transactions.*.paid' => 'required|numeric',
            'transactions.*.created_at' => 'required|string',
            'transactions.*.items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data transaksi offline tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $offlineList = $request->input('transactions', []);
        $syncedResults = [];

        $activeShift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        DB::beginTransaction();
        try {
            foreach ($offlineList as $offTx) {
                $subtotal = 0;
                foreach ($offTx['items'] as $item) {
                    $subtotal += ((float) $item['price'] * (int) $item['quantity']);
                }

                $discountPercent = (float) ($offTx['discount_percent'] ?? 0);
                $taxPercent = (float) ($offTx['tax_percent'] ?? 0);
                $discountAmount = ($subtotal * $discountPercent) / 100;
                $taxAmount = ($subtotal * $taxPercent) / 100;
                $total = $subtotal - $discountAmount + $taxAmount;
                $paid = (float) $offTx['paid'];
                $change = $offTx['payment_method'] === 'cash' ? max(0, $paid - $total) : 0;

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'shift_id' => $activeShift?->id,
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'tax' => $taxAmount,
                    'total' => $total,
                    'paid' => $paid,
                    'change' => $change,
                    'payment_method' => $offTx['payment_method'],
                    'order_type' => $offTx['order_type'],
                    'table_number' => $offTx['table_number'] ?? null,
                    'customer_name' => $offTx['customer_name'] ?? null,
                    'status' => 'completed',
                    'created_at' => $offTx['created_at'],
                ]);

                foreach ($offTx['items'] as $item) {
                    $product = Product::find($item['id']);
                    $hargaBeli = $product ? (float) ($product->harga_beli ?? 0) : 0;
                    $itemPrice = (float) $item['price'];
                    $itemQty = (int) $item['quantity'];

                    $totalAddonCost = 0;
                    if (!empty($item['addons'])) {
                        $addonIds = array_column($item['addons'], 'id');
                        $totalAddonCost = (float) Addon::whereIn('id', $addonIds)->sum('harga_beli');
                    }
                    $unitCost = $hargaBeli + $totalAddonCost;

                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['id'],
                        'quantity' => $itemQty,
                        'price' => $itemPrice,
                        'harga_beli' => $unitCost,
                        'subtotal' => $itemPrice * $itemQty,
                        'profit' => ($itemPrice - $unitCost) * $itemQty,
                        'notes' => $item['notes'] ?? null,
                        'addons' => !empty($item['addons']) ? $item['addons'] : null,
                    ]);
                }

                $syncedResults[] = [
                    'offline_id' => $offTx['offline_id'],
                    'server_id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'status' => 'synced',
                ];
            }

            DB::commit();

            if ($activeShift) {
                $activeShift->recalculateTotals();
            }

            return response()->json([
                'success' => true,
                'message' => count($syncedResults) . ' transaksi offline berhasil disinkronisasi ke server.',
                'synced_count' => count($syncedResults),
                'results' => $syncedResults,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi transaksi offline: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Format Shift Data response.
     */
    private function formatShiftData(CashierShift $shift): array
    {
        return [
            'id' => $shift->id,
            'user_id' => $shift->user_id,
            'status' => $shift->status,
            'start_time' => $shift->start_time?->format('Y-m-d H:i:s'),
            'end_time' => $shift->end_time?->format('Y-m-d H:i:s'),
            'starting_cash' => (float) $shift->starting_cash,
            'cash_sales' => (float) $shift->cash_sales,
            'qris_sales' => (float) $shift->qris_sales,
            'transfer_sales' => (float) $shift->transfer_sales,
            'total_sales' => (float) $shift->total_sales,
            'total_transactions' => (int) $shift->total_transactions,
            'expected_cash' => (float) $shift->expected_cash,
            'actual_cash' => !is_null($shift->actual_cash) ? (float) $shift->actual_cash : null,
            'difference' => !is_null($shift->difference) ? (float) $shift->difference : 0,
            'notes' => $shift->notes ?? '',
        ];
    }

    /**
     * Helper: Build Customer Receipt Payload for 58mm Printer (ESC/POS compatible).
     */
    private function buildCustomerReceiptPayload(Transaction $transaction): array
    {
        $setting = Setting::first();

        $items = $transaction->details->map(function ($d) {
            return [
                'name' => $d->product?->name ?? 'Menu',
                'quantity' => (int) $d->quantity,
                'price' => (float) $d->price,
                'subtotal' => (float) $d->subtotal,
                'notes' => $d->notes ?? '',
                'addons' => $d->addons ?? [],
            ];
        })->toArray();

        return [
            'header' => [
                'shop_name' => $setting->shop_name ?? 'POS Cafe',
                'address' => $setting->address ?? '',
                'phone' => $setting->phone ?? '',
            ],
            'invoice_number' => $transaction->invoice_number,
            'date' => $transaction->created_at->format('d/m/Y H:i'),
            'cashier_name' => $transaction->user?->name ?? 'Kasir',
            'order_type' => strtoupper(str_replace('_', ' ', $transaction->order_type)),
            'table_number' => $transaction->table_number ? 'Meja ' . $transaction->table_number : null,
            'customer_name' => $transaction->customer_name ?? null,
            'items' => $items,
            'summary' => [
                'subtotal' => (float) $transaction->subtotal,
                'discount' => (float) $transaction->discount,
                'tax' => (float) $transaction->tax,
                'total' => (float) $transaction->total,
                'payment_method' => strtoupper($transaction->payment_method),
                'paid' => (float) $transaction->paid,
                'change' => (float) $transaction->change,
            ],
            'footer' => [
                'message' => $setting->receipt_footer ?? 'Terima Kasih Atas Kunjungannya!',
                'wifi_name' => $setting->wifi_name ?? '',
                'wifi_password' => $setting->wifi_password ?? '',
            ],
            'paper_width' => 58,
        ];
    }

    /**
     * Helper: Build Shift Report Receipt Payload for 58mm Printer.
     */
    private function buildShiftReportReceipt(CashierShift $shift): array
    {
        $setting = Setting::first();

        return [
            'type' => 'SHIFT_REPORT',
            'header' => [
                'shop_name' => $setting->shop_name ?? 'POS Cafe',
                'title' => 'REKAP TUTUP SHIFT KASIR',
            ],
            'cashier_name' => $shift->user?->name ?? 'Kasir',
            'start_time' => $shift->start_time?->format('d/m/Y H:i'),
            'end_time' => $shift->end_time?->format('d/m/Y H:i'),
            'summary' => [
                'starting_cash' => (float) $shift->starting_cash,
                'cash_sales' => (float) $shift->cash_sales,
                'qris_sales' => (float) $shift->qris_sales,
                'transfer_sales' => (float) $shift->transfer_sales,
                'total_sales' => (float) $shift->total_sales,
                'total_transactions' => (int) $shift->total_transactions,
                'expected_cash' => (float) $shift->expected_cash,
                'actual_cash' => (float) $shift->actual_cash,
                'difference' => (float) $shift->difference,
            ],
            'notes' => $shift->notes ?? '',
            'paper_width' => 58,
        ];
    }

    /**
     * Get Menu & Category Availability List.
     */
    public function getAvailability(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $categories = Category::withCount('products')->orderBy('name', 'asc')->get()->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'is_active' => (bool) ($cat->is_active ?? true),
                'products_count' => (int) $cat->products_count,
            ];
        });

        $productQuery = Product::with('category')->orderBy('name', 'asc');

        if ($search) {
            $productQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $productQuery->where('category_id', $categoryId);
        }

        $products = $productQuery->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'category_id' => $p->category_id,
                'category_name' => $p->category?->name ?? 'Menu',
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'price' => (float) $p->price,
                'image_url' => $p->image ? asset('storage/' . $p->image) : null,
                'is_active' => (bool) $p->is_active,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'products' => $products,
            ],
        ]);
    }

    /**
     * Toggle Product Availability.
     */
    public function toggleProductAvailability($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $product->is_active = !$product->is_active;
        $product->save();

        $statusLabel = $product->is_active ? 'Tersedia di Kasir' : 'Habis / Kosong';

        return response()->json([
            'success' => true,
            'message' => "Menu '{$product->name}' berhasil diubah menjadi {$statusLabel}.",
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'is_active' => (bool) $product->is_active,
            ],
        ]);
    }

    /**
     * Toggle Category Availability.
     */
    public function toggleCategoryAvailability($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }

        $category->is_active = !($category->is_active ?? true);
        $category->save();

        $statusLabel = $category->is_active ? 'Aktif di POS' : 'Non-Aktif (Disembunyikan)';

        return response()->json([
            'success' => true,
            'message' => "Kategori '{$category->name}' berhasil diubah menjadi {$statusLabel}.",
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'is_active' => (bool) $category->is_active,
            ],
        ]);
    }

    // ==========================================
    // ONLINE ORDERS (PESANAN MASUK SELF-ORDER)
    // ==========================================

    /**
     * Get list of incoming online orders with status filters, search, and live stats.
     */
    public function getOnlineOrders(Request $request)
    {
        $status = $request->input('status', 'active'); // 'active', 'pending', 'processing', 'ready', 'completed', 'cancelled', 'all'
        $search = trim($request->input('search', ''));
        $date = $request->input('date');

        // 1. Stats Counter (Online Orders Today)
        $todayOnlineQuery = Transaction::where('order_source', 'self_order')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', today());

        $statsPending = (clone $todayOnlineQuery)->where('status', 'pending')->count();
        $statsProcessing = (clone $todayOnlineQuery)->where('status', 'processing')->count();
        $statsReady = (clone $todayOnlineQuery)->where('status', 'ready')->count();
        $statsActive = (clone $todayOnlineQuery)->whereIn('status', ['pending', 'processing', 'ready'])->count();
        $statsCompleted = (clone $todayOnlineQuery)->where('status', 'completed')->count();
        $statsRevenueToday = (float) (clone $todayOnlineQuery)->where('status', 'completed')->sum('total');

        $setting = Setting::first();
        $isOnlineOrderActive = (bool) ($setting->is_online_order_active ?? true);

        // 2. Query Orders List
        $query = Transaction::with(['details.product', 'user', 'shift'])
            ->where('order_source', 'self_order')
            ->where('payment_status', 'paid')
            ->latest('id');

        if ($status === 'active') {
            $query->whereIn('status', ['pending', 'processing', 'ready']);
        } elseif ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'processing') {
            $query->where('status', 'processing');
        } elseif ($status === 'ready') {
            $query->where('status', 'ready');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
            if ($date) {
                $query->whereDate('created_at', $date);
            } else {
                $query->whereDate('created_at', today());
            }
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
            if ($date) {
                $query->whereDate('created_at', $date);
            }
        } elseif ($date) {
            $query->whereDate('created_at', $date);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('table_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 0);
        if ($perPage > 0) {
            $paginator = $query->paginate($perPage);
            $ordersCollection = $paginator->getCollection();
            $paginationMeta = [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ];
        } else {
            $ordersCollection = $query->limit(50)->get();
            $paginationMeta = null;
        }

        $formattedOrders = $ordersCollection->map(function ($tx) {
            return $this->formatOnlineOrder($tx);
        });

        return response()->json([
            'success' => true,
            'stats' => [
                'active' => $statsActive,
                'pending' => $statsPending,
                'processing' => $statsProcessing,
                'ready' => $statsReady,
                'completed_today' => $statsCompleted,
                'revenue_today' => $statsRevenueToday,
                'is_online_order_active' => $isOnlineOrderActive,
            ],
            'data' => $formattedOrders,
            'pagination' => $paginationMeta,
        ]);
    }

    /**
     * Check / Poll for incoming new online orders for Sound & Alert notifications.
     */
    public function checkNewOnlineOrders(Request $request)
    {
        $lastOrderId = (int) $request->input('last_order_id', 0);

        $newOrders = Transaction::with(['details.product'])
            ->where('order_source', 'self_order')
            ->where('payment_status', 'paid')
            ->where('id', '>', $lastOrderId)
            ->oldest('id')
            ->get();

        $hasNew = $newOrders->isNotEmpty();
        $latestOrder = Transaction::where('order_source', 'self_order')
            ->where('payment_status', 'paid')
            ->max('id');

        $setting = Setting::first();
        $isOnlineOrderActive = (bool) ($setting->is_online_order_active ?? true);

        // Active count online
        $activeCount = Transaction::where('order_source', 'self_order')
            ->where('payment_status', 'paid')
            ->whereIn('status', ['pending', 'processing', 'ready'])
            ->count();

        // Active open bills count (dine-in / offline open bills)
        $openBillsCount = Transaction::where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('order_source')
                  ->orWhere('order_source', '!=', 'self_order');
            })
            ->count();

        $formattedNewOrders = $newOrders->map(function ($order) {
            return [
                'id' => $order->id,
                'invoice_number' => $order->invoice_number,
                'short_order_number' => $order->short_order_number,
                'customer_name' => $order->customer_name ?? 'Pelanggan',
                'customer_phone' => $order->customer_phone ?? '',
                'table_number' => $order->table_number ?? null,
                'order_type' => $order->order_type,
                'total' => (float) $order->total,
                'formatted_total' => 'Rp ' . number_format($order->total, 0, ',', '.'),
                'items_count' => $order->details->count(),
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'time_ago' => $order->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'has_new_orders' => $hasNew,
            'new_orders_count' => $newOrders->count(),
            'latest_order_id' => $latestOrder ?? $lastOrderId,
            'active_orders_count' => $activeCount,
            'open_bills_count' => $openBillsCount,
            'is_online_order_active' => $isOnlineOrderActive,
            'new_orders' => $formattedNewOrders,
        ]);
    }

    /**
     * Get live statistics of Online Orders.
     */
    public function getOnlineOrdersStats(Request $request)
    {
        $todayOnlineQuery = Transaction::where('order_source', 'self_order')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', today());

        $statsPending = (clone $todayOnlineQuery)->where('status', 'pending')->count();
        $statsProcessing = (clone $todayOnlineQuery)->where('status', 'processing')->count();
        $statsReady = (clone $todayOnlineQuery)->where('status', 'ready')->count();
        $statsActive = (clone $todayOnlineQuery)->whereIn('status', ['pending', 'processing', 'ready'])->count();
        $statsCompleted = (clone $todayOnlineQuery)->where('status', 'completed')->count();
        $statsCancelled = (clone $todayOnlineQuery)->where('status', 'cancelled')->count();
        $statsRevenueToday = (float) (clone $todayOnlineQuery)->where('status', 'completed')->sum('total');

        $setting = Setting::first();
        $isOnlineOrderActive = (bool) ($setting->is_online_order_active ?? true);

        return response()->json([
            'success' => true,
            'data' => [
                'active' => $statsActive,
                'pending' => $statsPending,
                'processing' => $statsProcessing,
                'ready' => $statsReady,
                'completed_today' => $statsCompleted,
                'cancelled_today' => $statsCancelled,
                'revenue_today' => $statsRevenueToday,
                'formatted_revenue_today' => 'Rp ' . number_format($statsRevenueToday, 0, ',', '.'),
                'is_online_order_active' => $isOnlineOrderActive,
            ],
        ]);
    }

    /**
     * Get full details of a specific Online Order.
     */
    public function getOnlineOrderDetail($id)
    {
        $transaction = Transaction::with(['details.product', 'user', 'shift', 'cancelledBy'])
            ->where('order_source', 'self_order')
            ->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan online tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatOnlineOrder($transaction, true),
        ]);
    }

    /**
     * Update status of an Online Order (processing, ready, completed, cancelled).
     */
    public function updateOnlineOrderStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,ready,completed,cancelled',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $transaction = Transaction::where('order_source', 'self_order')->find($id);
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan online tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();
        $newStatus = $request->input('status');

        $activeShift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        $updateData = [
            'status' => $newStatus,
            'user_id' => $user->id,
        ];

        if ($activeShift) {
            $updateData['shift_id'] = $activeShift->id;
        }

        if ($newStatus === 'cancelled') {
            $updateData['cancelled_reason'] = $request->input('reason') ?: 'Dibatalkan oleh Kasir via Mobile POS';
            $updateData['cancelled_by'] = $user->id;
            $updateData['cancelled_at'] = now();
        }

        $transaction->update($updateData);

        if ($activeShift) {
            $activeShift->recalculateTotals();
        }

        $statusLabels = [
            'pending' => 'Menunggu Diproses',
            'processing' => 'Sedang Disiapkan di Dapur',
            'ready' => 'Siap Diambil / Diantar',
            'completed' => 'Pesanan Selesai',
            'cancelled' => 'Pesanan Dibatalkan',
        ];

        $label = $statusLabels[$newStatus] ?? $newStatus;

        return response()->json([
            'success' => true,
            'message' => "Pesanan {$transaction->invoice_number} berhasil diubah ke status: {$label}.",
            'data' => $this->formatOnlineOrder($transaction->fresh(['details.product', 'user', 'shift'])),
        ]);
    }

    /**
     * Toggle or set Store Acceptance for Online Orders.
     */
    public function toggleOnlineOrderActive(Request $request)
    {
        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create(['is_online_order_active' => true]);
        }

        if ($request->has('is_active')) {
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        } else {
            $isActive = !((bool) ($setting->is_online_order_active ?? true));
        }

        $setting->update(['is_online_order_active' => $isActive]);

        $message = $isActive
            ? 'Pesanan Online DIBUKA (Toko menerima pesanan online).'
            : 'Pesanan Online DIJEDA SEMENTARA (Toko sedang sibuk).';

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_online_order_active' => $isActive,
        ]);
    }

    /**
     * Get Receipt Data & ESC/POS RawBT for Online Order.
     */
    public function getOnlineOrderReceipt($id)
    {
        $transaction = Transaction::with(['details.product', 'user', 'shift'])
            ->where('order_source', 'self_order')
            ->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan online tidak ditemukan.',
            ], 404);
        }

        $setting = Setting::first();
        $rawbt = base64_encode(ReceiptPrintService::buildTransactionEscPos($transaction, $setting));

        return response()->json([
            'success' => true,
            'data' => [
                'receipt_payload' => $this->buildCustomerReceiptPayload($transaction),
                'rawbt_base64' => $rawbt,
            ],
        ]);
    }

    /**
     * Get Kitchen Slip Data & ESC/POS RawBT for Online Order.
     */
    public function getOnlineOrderKitchenSlip($id)
    {
        $transaction = Transaction::with(['details.product', 'user', 'shift'])
            ->where('order_source', 'self_order')
            ->find($id);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan online tidak ditemukan.',
            ], 404);
        }

        $setting = Setting::first();
        $rawbtKitchen = base64_encode(ReceiptPrintService::buildKitchenEscPos($transaction, $setting));

        return response()->json([
            'success' => true,
            'data' => [
                'invoice_number' => $transaction->invoice_number,
                'table_number' => $transaction->table_number,
                'customer_name' => $transaction->customer_name,
                'order_type' => $transaction->order_type,
                'items' => $transaction->details->map(function ($d) {
                    return [
                        'name' => $d->product?->name ?? 'Menu',
                        'quantity' => (int) $d->quantity,
                        'notes' => $d->notes ?? '',
                        'addons' => $d->addons ?? [],
                    ];
                }),
                'rawbt_base64' => $rawbtKitchen,
            ],
        ]);
    }

    /**
     * Helper: Format Online Order resource payload.
     */
    private function formatOnlineOrder(Transaction $tx, bool $isDetail = false): array
    {
        $statusLabels = [
            'pending' => 'Menunggu Diproses',
            'processing' => 'Sedang Disiapkan',
            'ready' => 'Siap Diambil/Diantar',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $items = $tx->details->map(function ($d) {
            $product = $d->product;
            $imageUrl = null;
            if ($product && $product->image) {
                $imageUrl = Str::startsWith($product->image, 'http')
                    ? $product->image
                    : asset('storage/' . $product->image);
            }

            return [
                'id' => $d->id,
                'product_id' => $d->product_id,
                'product_name' => $product?->name ?? 'Menu',
                'product_image' => $imageUrl,
                'quantity' => (int) $d->quantity,
                'price' => (float) $d->price,
                'subtotal' => (float) $d->subtotal,
                'notes' => $d->notes ?? '',
                'addons' => $d->addons ?? [],
            ];
        });

        $totalQty = $tx->details->sum('quantity');

        $data = [
            'id' => $tx->id,
            'invoice_number' => $tx->invoice_number,
            'short_order_number' => $tx->short_order_number,
            'order_token' => $tx->order_token,
            'order_source' => $tx->order_source ?? 'self_order',
            'order_type' => $tx->order_type ?? 'dine_in',
            'table_number' => $tx->table_number ?? null,
            'customer_name' => $tx->customer_name ?? 'Pelanggan',
            'customer_phone' => $tx->customer_phone ?? '',
            'status' => $tx->status,
            'status_label' => $statusLabels[$tx->status] ?? $tx->status,
            'payment_status' => $tx->payment_status ?? 'paid',
            'payment_method' => $tx->payment_method ?? 'midtrans',
            'subtotal' => (float) $tx->subtotal,
            'discount' => (float) $tx->discount,
            'tax' => (float) $tx->tax,
            'total' => (float) $tx->total,
            'paid' => (float) $tx->paid,
            'change' => (float) $tx->change,
            'total_qty' => (int) $totalQty,
            'items_count' => $tx->details->count(),
            'items' => $items,
            'cashier_name' => $tx->user?->name ?? null,
            'shift_id' => $tx->shift_id ?? null,
            'cancelled_reason' => $tx->cancelled_reason ?? null,
            'cancelled_at' => $tx->cancelled_at?->format('Y-m-d H:i:s'),
            'created_at' => $tx->created_at->format('Y-m-d H:i:s'),
            'created_at_human' => $tx->created_at->format('d M Y, H:i'),
            'time_ago' => $tx->created_at->diffForHumans(),
        ];

        return $data;
    }
}
