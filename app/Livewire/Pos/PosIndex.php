<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\CashierShift;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('POS Kasir - POS Cafe')]
class PosIndex extends Component
{
    public $products = [];
    public $selectedCategory = '';
    public $search = '';
    public $cart = [];
    public $subtotal = 0;
    public $discount = 0;
    public $tax = 0;
    public $total = 0;
    public $paymentMethod = 'cash';
    public $paid = 0;
    public $change = 0;
    
    // Cafe Specific State
    public $orderType = 'dine_in'; // dine_in, take_away, delivery
    public $tableNumber = '';
    public $selectedTable = '';
    public $customTableNumber = '';
    public $isCustomTable = false;
    public $customerName = '';

    // Shift Management State
    public $activeShift = null;
    public $showStartShiftModal = false;
    public $showEndShiftModal = false;
    public $startingCash = 0;
    public $formattedStartingCash = '0';
    public $actualCash = 0;
    public $formattedActualCash = '0';
    public $shiftDifference = 0;
    public $shiftNotes = '';

    // Item Customization State
    public $showItemNotesModal = false;
    public $editingItemIndex = null;
    public $tempItemNotes = '';
    public $tempSugarLevel = 'Normal';
    public $tempIceLevel = 'Normal';

    // State Modals
    public $showPaymentModal = false;
    public $showSuccessModal = false;
    public $showMobileCart = false;

    // Open Bill / Hold Orders State
    public $currentOpenBillId = null;
    public $currentOpenBillInvoice = '';
    public $showOpenBillsModal = false;
    public $openBillsSearch = '';
    public $preBillTransaction = null;
    public $showPreBillModal = false;

    // Quick Menu & Category Availability Modal State (Item 86)
    public $showAvailabilityModal = false;
    public $availabilityTab = 'products'; // 'products' or 'categories'
    public $availabilitySearch = '';
    public $availabilityCategoryFilter = '';
    public $lastInvoice = '';
    public $lastTransaction = null;

    public function isCashierRole(): bool
    {
        return in_array(auth()->user()?->role, ['kasir', 'cashier']);
    }

    public function mount()
    {
        $this->loadProducts();
        $this->checkActiveShift();
    }

    public function checkActiveShift()
    {
        $this->activeShift = CashierShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($this->activeShift) {
            $this->activeShift->recalculateTotals();
        }
    }

    public function openStartShiftModal()
    {
        $this->startingCash = 0;
        $this->formattedStartingCash = '0';
        $this->showStartShiftModal = true;
    }

    public function closeStartShiftModal()
    {
        $this->showStartShiftModal = false;
    }

    public function setStartingCashPreset($amount)
    {
        $this->startingCash = (float) $amount;
        $this->formattedStartingCash = number_format($this->startingCash, 0, ',', '.');
    }

    public function updatedFormattedStartingCash()
    {
        $clean = preg_replace('/[^0-9]/', '', $this->formattedStartingCash);
        $this->startingCash = (float) ($clean ?: 0);
        $this->formattedStartingCash = number_format($this->startingCash, 0, ',', '.');
    }

    public function startShift()
    {
        if ($this->startingCash < 0) {
            $this->notify('error', 'Modal awal tidak boleh negatif.');
            return;
        }

        $shift = CashierShift::create([
            'user_id' => auth()->id(),
            'start_time' => now(),
            'starting_cash' => (float) $this->startingCash,
            'expected_cash' => (float) $this->startingCash,
            'status' => 'open',
        ]);

        $this->activeShift = $shift;
        $this->showStartShiftModal = false;
        $this->notify('success', 'Shift kasir berhasil dibuka dengan modal Rp ' . number_format($this->startingCash, 0, ',', '.'));
    }

    public function openEndShiftModal()
    {
        $this->checkActiveShift();
        if (!$this->activeShift) {
            $this->notify('error', 'Tidak ada shift aktif yang perlu ditutup.');
            return;
        }

        // GUARD: Cek apakah masih ada Bill Aktif (Open Bill / Pending) yang belum diselesaikan
        $pendingBillsCount = Transaction::where('status', 'pending')->count();
        if ($pendingBillsCount > 0) {
            $this->notify('error', "Tidak dapat menutup shift! Masih ada {$pendingBillsCount} Bill Aktif (Open Bill) yang belum diselesaikan.");
            $this->openOpenBillsModal();
            return;
        }

        $this->actualCash = (float) $this->activeShift->expected_cash;
        $this->formattedActualCash = number_format($this->actualCash, 0, ',', '.');
        $this->calculateShiftDifference();
        $this->shiftNotes = '';
        $this->showEndShiftModal = true;
    }

    public function closeEndShiftModal()
    {
        $this->showEndShiftModal = false;
    }

    public function updatedFormattedActualCash()
    {
        $clean = preg_replace('/[^0-9]/', '', $this->formattedActualCash);
        $this->actualCash = (float) ($clean ?: 0);
        $this->formattedActualCash = number_format($this->actualCash, 0, ',', '.');
        $this->calculateShiftDifference();
    }

    public function calculateShiftDifference()
    {
        if ($this->activeShift) {
            $this->shiftDifference = (float) $this->actualCash - (float) $this->activeShift->expected_cash;
        }
    }

    public function endShift()
    {
        $this->checkActiveShift();
        if (!$this->activeShift) {
            $this->notify('error', 'Tidak ada shift aktif yang perlu ditutup.');
            return;
        }

        // GUARD: Cek kembali saat submit endShift
        $pendingBillsCount = Transaction::where('status', 'pending')->count();
        if ($pendingBillsCount > 0) {
            $this->notify('error', "Tidak dapat menutup shift! Masih ada {$pendingBillsCount} Bill Aktif (Open Bill) yang belum diselesaikan.");
            $this->showEndShiftModal = false;
            $this->openOpenBillsModal();
            return;
        }

        $this->activeShift->end_time = now();
        $this->activeShift->actual_cash = (float) $this->actualCash;
        $this->activeShift->difference = (float) $this->actualCash - (float) $this->activeShift->expected_cash;
        $this->activeShift->notes = $this->shiftNotes;
        $this->activeShift->status = 'closed';
        $this->activeShift->save();

        $this->activeShift = null;
        $this->showEndShiftModal = false;

        $this->notify('success', 'Shift kasir berhasil ditutup!');
    }

    // Helper Notifikasi Toast
    public function notify($type, $message)
    {
        $this->dispatch('show-toast', type: $type, message: $message);
    }

    public function selectCategory($categoryId = '')
    {
        $this->selectedCategory = $categoryId;
        $this->loadProducts();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->loadProducts();
    }

    public function getCartQuantity($productId)
    {
        $qty = 0;
        foreach ($this->cart as $item) {
            if ($item['id'] == $productId) {
                $qty += $item['quantity'];
            }
        }
        return $qty;
    }

    public function updatedSelectedTable($val)
    {
        if ($val === 'custom') {
            $this->isCustomTable = true;
            $this->tableNumber = $this->customTableNumber;
        } else {
            $this->isCustomTable = false;
            $this->tableNumber = $val;
        }
    }

    public function updatedCustomTableNumber($val)
    {
        if ($this->isCustomTable) {
            $this->tableNumber = $val;
        }
    }

    public function setOrderType($type)
    {
        $this->orderType = $type;
        if ($type !== 'dine_in') {
            $this->tableNumber = '';
            $this->selectedTable = '';
            $this->customTableNumber = '';
            $this->isCustomTable = false;
        } else {
            $this->customerName = '';
        }
    }

    public function loadProducts()
    {
        // For Cafe Mode: display all menu items from active categories.
        // Active products are sorted first, inactive products sink to the bottom.
        $query = Product::whereHas('category', function ($q) {
                $q->where('is_active', true);
            })
            ->with('category');

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', $this->search);
            });
        }

        $query->withSum('transactionDetails as total_sold', 'quantity')
            ->orderByDesc('is_active')
            ->orderByDesc('total_sold')
            ->orderBy('name', 'asc');

        $this->products = $query->get();
    }

    public function addToCart($productId)
    {
        // 1. Cari produk langsung dari koleksi memory yang sudah dimuat (Super Cepat 0 ms query)
        $product = $this->products ? $this->products->firstWhere('id', $productId) : null;
        if (!$product) {
            $product = Product::with('category')->find($productId);
        }

        if (!$product || !$product->category || !$product->category->is_active) {
            $this->notify('error', 'Kategori menu ini sedang tidak aktif di POS.');
            return;
        }

        if (!$product->is_active) {
            $this->notify('error', "Menu '{$product->name}' sedang tidak tersedia (Non-Aktif).");
            return;
        }

        // 2. Cek apakah produk sudah ada di keranjang
        $existingIndex = null;
        foreach ($this->cart as $index => $item) {
            if ($item['id'] === $productId && empty($item['notes'])) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            $this->cart[$existingIndex]['quantity']++;
            $this->cart[$existingIndex]['subtotal'] = $this->cart[$existingIndex]['quantity'] * $this->cart[$existingIndex]['price'];
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'category_name' => $product->category->name ?? '',
                'price' => $product->price,
                'harga_beli' => $product->harga_beli,
                'quantity' => 1,
                'subtotal' => $product->price,
                'notes' => ''
            ];
        }

        $this->calculateTotal();
    }

    public function scanBarcode()
    {
        $barcode = trim($this->search);

        if (empty($barcode)) {
            return;
        }

        $product = Product::where('barcode', $barcode)
            ->whereHas('category', function ($q) {
                $q->where('is_active', true);
            })
            ->first();

        if ($product) {
            if (!$product->is_active) {
                $this->notify('error', "Menu '{$product->name}' ditemukan tetapi berstatus Non-Aktif / Tidak Tersedia.");
                return;
            }
            $this->addToCart($product->id);
            $this->search = '';
            $this->loadProducts();
        } else {
            $this->notify('info', 'Barcode tidak ditemukan pada daftar menu.');
        }
    }

    public function openItemNotesModal($index)
    {
        if (!isset($this->cart[$index])) return;

        $this->editingItemIndex = $index;
        $currentNotes = $this->cart[$index]['notes'] ?? '';
        
        $this->tempItemNotes = $currentNotes;
        $this->tempSugarLevel = 'Normal';
        $this->tempIceLevel = 'Normal';

        // Parse existing preset options if present
        if (str_contains($currentNotes, 'Sugar:')) {
            preg_match('/Sugar:\s*([^,|]+)/', $currentNotes, $sugarMatch);
            if (!empty($sugarMatch[1])) $this->tempSugarLevel = trim($sugarMatch[1]);
        }
        if (str_contains($currentNotes, 'Ice:')) {
            preg_match('/Ice:\s*([^,|]+)/', $currentNotes, $iceMatch);
            if (!empty($iceMatch[1])) $this->tempIceLevel = trim($iceMatch[1]);
        }

        $this->showItemNotesModal = true;
    }

    public function saveItemNotes()
    {
        if ($this->editingItemIndex === null || !isset($this->cart[$this->editingItemIndex])) return;

        $notesParts = [];
        $categoryName = strtolower($this->cart[$this->editingItemIndex]['category_name'] ?? '');
        if (str_contains($categoryName, 'coffee') || str_contains($categoryName, 'tea') || str_contains($categoryName, 'minuman')) {
            if ($this->tempSugarLevel !== 'Normal') {
                $notesParts[] = $this->tempSugarLevel;
            }
            if ($this->tempIceLevel !== 'Normal') {
                $notesParts[] = $this->tempIceLevel;
            }
        }

        if (!empty(trim($this->tempItemNotes))) {
            $notesParts[] = trim($this->tempItemNotes);
        }

        $this->cart[$this->editingItemIndex]['notes'] = implode(' | ', $notesParts);
        $this->showItemNotesModal = false;
        $this->editingItemIndex = null;
        $this->notify('success', 'Catatan pesanan diperbarui.');
    }

    public function closeItemNotesModal()
    {
        $this->showItemNotesModal = false;
        $this->editingItemIndex = null;
    }

    public function updateQuantity($index, $action)
    {
        if ($action === 'increase') {
            $this->cart[$index]['quantity']++;
        } elseif ($action === 'decrease') {
            if ($this->cart[$index]['quantity'] > 1) {
                $this->cart[$index]['quantity']--;
            }
        }

        $this->cart[$index]['subtotal'] = $this->cart[$index]['quantity'] * $this->cart[$index]['price'];
        $this->calculateTotal();
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotal();
        $this->showMobileCart = false;
    }

    public function resetTransaction()
    {
        $this->cart = [];
        $this->subtotal = 0;
        $this->discount = 0;
        $this->tax = 0;
        $this->total = 0;
        $this->paid = 0;
        $this->change = 0;
        $this->paymentMethod = 'cash';
        $this->orderType = 'dine_in';
        $this->tableNumber = '';
        $this->selectedTable = '';
        $this->customTableNumber = '';
        $this->isCustomTable = false;
        $this->customerName = '';
        $this->currentOpenBillId = null;
        $this->currentOpenBillInvoice = '';
        $this->loadProducts();
        $this->showMobileCart = false;
    }

    public function calculateTotal()
    {
        $this->subtotal = array_sum(array_column($this->cart, 'subtotal'));
        $discountPercent = is_numeric($this->discount) ? (float) $this->discount : 0;
        $taxPercent = is_numeric($this->tax) ? (float) $this->tax : 0;
        $discountAmount = ($this->subtotal * $discountPercent) / 100;
        $taxAmount = ($this->subtotal * $taxPercent) / 100;
        $this->total = (float) $this->subtotal - $discountAmount + $taxAmount;
    }

    public function openMobileCart() { $this->showMobileCart = true; }
    public function closeMobileCart() { $this->showMobileCart = false; }
    public function updatedSelectedCategory() { $this->loadProducts(); }
    public function updatedSearch() { $this->loadProducts(); }
    public function updatedDiscount() { $this->calculateTotal(); }
    public function updatedTax() { $this->calculateTotal(); }
    
    public function openPaymentModal()
    {
        if (empty($this->cart)) return;

        if ($this->isCashierRole() && !$this->activeShift) {
            $this->openStartShiftModal();
            return;
        }

        $this->showPaymentModal = true;
        if ($this->paymentMethod !== 'cash') {
            $this->paid = $this->total;
            $this->change = 0;
        } else {
            $this->paid = $this->total;
            $this->calculateChange();
        }
    }

    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        if ($method !== 'cash') {
            $this->paid = $this->total;
            $this->change = 0;
        } else {
            $this->calculateChange();
        }
    }

    public function updatedPaymentMethod()
    {
        if ($this->paymentMethod !== 'cash') {
            $this->paid = $this->total;
            $this->change = 0;
        } else {
            $this->calculateChange();
        }
    }

    public function updatedPaid()
    {
        if ($this->paymentMethod === 'cash') {
            $this->calculateChange();
        } else {
            $this->paid = $this->total;
            $this->change = 0;
        }
    }
    
    public function setExactPaid()
    {
        $this->paid = $this->total;
        $this->calculateChange();
    }

    public function setPaidAmount($amount)
    {
        if ($this->paymentMethod === 'cash') {
            $this->paid = (float) $amount;
            $this->calculateChange();
        }
    }

    public function calculateChange()
    {
        if ($this->paymentMethod !== 'cash') {
            $this->change = 0;
            return;
        }
        $paid = is_numeric($this->paid) ? (float) $this->paid : 0;
        $this->change = max(0, $paid - (float) $this->total);
    }
    public function closePaymentModal() { $this->showPaymentModal = false; }
    public function closeSuccessModal() { $this->showSuccessModal = false; }

    public function openOpenBillsModal()
    {
        $this->showOpenBillsModal = true;
        $this->openBillsSearch = '';
    }

    public function closeOpenBillsModal()
    {
        $this->showOpenBillsModal = false;
    }

    public function openPreBillModal($transactionId)
    {
        $this->preBillTransaction = Transaction::with(['details.product', 'user'])->find($transactionId);
        if ($this->preBillTransaction) {
            $this->showPreBillModal = true;
        }
    }

    public function closePreBillModal()
    {
        $this->showPreBillModal = false;
        $this->preBillTransaction = null;
    }

    public function saveOpenBill()
    {
        if (empty($this->cart)) {
            $this->notify('error', 'Keranjang masih kosong.');
            return;
        }

        if ($this->orderType !== 'dine_in') {
            $this->notify('error', 'Fitur Simpan Bill hanya berlaku untuk pesanan Dine In (Makan di Tempat).');
            return;
        }

        if ($this->isCashierRole() && !$this->activeShift) {
            $this->openStartShiftModal();
            return;
        }

        // Pastikan ada identitas meja/nama pelanggan agar pesanan tidak tertukar
        if (empty($this->tableNumber) && empty($this->customerName)) {
            $this->notify('error', 'Silakan pilih Nomor Meja atau isi Nama Pelanggan untuk Open Bill.');
            return;
        }

        DB::beginTransaction();
        try {
            $discountPercent = (float) $this->discount;
            $taxPercent = (float) $this->tax;
            $finalDiscountAmount = ($this->subtotal * $discountPercent) / 100;
            $finalTaxAmount = ($this->subtotal * $taxPercent) / 100;
            $total = (float) $this->total;

            if ($this->currentOpenBillId) {
                // Update Open Bill yang sedang aktif/diedit
                $transaction = Transaction::find($this->currentOpenBillId);
                if (!$transaction || $transaction->status !== 'pending') {
                    $this->notify('error', 'Bill tidak ditemukan atau sudah diselesaikan.');
                    DB::rollBack();
                    return;
                }

                $transaction->update([
                    'subtotal' => (float) $this->subtotal,
                    'discount' => $finalDiscountAmount,
                    'tax' => $finalTaxAmount,
                    'total' => $total,
                    'order_type' => $this->orderType,
                    'table_number' => $this->orderType === 'dine_in' ? ($this->tableNumber ?: null) : null,
                    'customer_name' => $this->customerName ?: null,
                ]);

                $transaction->details()->delete();
            } else {
                // Buat Open Bill baru
                $shiftId = $this->activeShift?->id;
                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $shiftId,
                    'subtotal' => (float) $this->subtotal,
                    'discount' => $finalDiscountAmount,
                    'tax' => $finalTaxAmount,
                    'total' => $total,
                    'paid' => 0,
                    'change' => 0,
                    'payment_method' => 'cash',
                    'order_type' => $this->orderType,
                    'table_number' => $this->orderType === 'dine_in' ? ($this->tableNumber ?: null) : null,
                    'customer_name' => $this->customerName ?: null,
                    'status' => 'pending',
                ]);
            }

            foreach ($this->cart as $item) {
                $harga_beli = $item['harga_beli'] ?? 0;
                $profit = ($item['price'] - $harga_beli) * $item['quantity'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'harga_beli' => $harga_beli,
                    'subtotal' => $item['subtotal'],
                    'profit' => $profit,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            $identifier = $transaction->table_number ? 'Meja ' . $transaction->table_number : ($transaction->customer_name ?: $transaction->invoice_number);
            
            $setting = Setting::first();
            $autoPrintKitchen = (bool) ($setting->auto_print_kitchen ?? false);

            $this->notify('success', "Pesanan {$identifier} berhasil disimpan (Open Bill).");

            if ($autoPrintKitchen) {
                $this->dispatch('print-kitchen-ticket', invoice: $transaction->invoice_number);
            }

            $this->resetTransaction();
            $this->showMobileCart = false;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->notify('error', 'Gagal menyimpan Open Bill: ' . $e->getMessage());
        }
    }

    public function resumeOpenBill($transactionId)
    {
        $transaction = Transaction::with(['details.product'])->find($transactionId);
        if (!$transaction || $transaction->status !== 'pending') {
            $this->notify('error', 'Bill tidak ditemukan atau sudah selesai.');
            return;
        }

        $this->currentOpenBillId = $transaction->id;
        $this->currentOpenBillInvoice = $transaction->invoice_number;
        $this->orderType = $transaction->order_type ?? 'dine_in';
        $this->tableNumber = $transaction->table_number ?? '';
        $this->selectedTable = $transaction->table_number ?? '';
        $this->customerName = $transaction->customer_name ?? '';

        $this->discount = ($transaction->subtotal > 0 && $transaction->discount > 0) ? round(($transaction->discount / $transaction->subtotal) * 100) : 0;
        $this->tax = ($transaction->subtotal > 0 && $transaction->tax > 0) ? round(($transaction->tax / $transaction->subtotal) * 100) : 0;

        $this->cart = [];
        foreach ($transaction->details as $d) {
            $this->cart[] = [
                'id' => $d->product_id,
                'name' => $d->product->name ?? 'Menu',
                'price' => (float) $d->price,
                'harga_beli' => (float) $d->harga_beli,
                'quantity' => (int) $d->quantity,
                'subtotal' => (float) $d->subtotal,
                'notes' => $d->notes ?? '',
                'sugar_level' => 'Normal',
                'ice_level' => 'Normal',
            ];
        }

        $this->calculateTotal();
        $this->showOpenBillsModal = false;
        $label = $transaction->table_number ? 'Meja ' . $transaction->table_number : ($transaction->customer_name ?: $transaction->invoice_number);
        $this->notify('info', "Memuat Bill {$label} ke keranjang.");
    }

    public function cancelOpenBill($transactionId)
    {
        $transaction = Transaction::find($transactionId);
        if (!$transaction || $transaction->status !== 'pending') {
            $this->notify('error', 'Bill tidak ditemukan.');
            return;
        }

        $transaction->update([
            'status' => 'cancelled',
            'cancelled_reason' => 'Dibatalkan Kasir sebelum Bayar (Void Open Bill)',
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
        ]);

        if ($this->currentOpenBillId === $transaction->id) {
            $this->resetTransaction();
        }

        $this->notify('info', "Bill {$transaction->invoice_number} berhasil dibatalkan.");
    }

    public function processPayment()
    {
        if ($this->isCashierRole() && !$this->activeShift) {
            $this->openStartShiftModal();
            return;
        }

        $paid = (float) $this->paid;
        $total = (float) $this->total;      

        if ($paid < $total) {
            $this->notify('error', 'Jumlah pembayaran kurang.');
            return;
        }

        DB::beginTransaction();
        try {
            $discountPercent = (float) $this->discount;
            $taxPercent = (float) $this->tax;
            $finalDiscountAmount = ($this->subtotal * $discountPercent) / 100;
            $finalTaxAmount = ($this->subtotal * $taxPercent) / 100;

            $shiftId = $this->activeShift?->id;

            if ($this->currentOpenBillId) {
                // Selesaikan Open Bill yang sudah ada
                $transaction = Transaction::find($this->currentOpenBillId);
                if ($transaction) {
                    $transaction->update([
                        'shift_id' => $shiftId,
                        'subtotal' => (float) $this->subtotal,
                        'discount' => $finalDiscountAmount,
                        'tax' => $finalTaxAmount,
                        'total' => $total,
                        'paid' => $paid,
                        'change' => (float) $this->change,
                        'payment_method' => $this->paymentMethod,
                        'order_type' => $this->orderType,
                        'table_number' => $this->orderType === 'dine_in' ? ($this->tableNumber ?: null) : null,
                        'customer_name' => $this->customerName ?: null,
                        'status' => 'completed',
                    ]);

                    $transaction->details()->delete();
                } else {
                    $transaction = Transaction::create([
                        'user_id' => auth()->id(),
                        'shift_id' => $shiftId,
                        'subtotal' => (float) $this->subtotal,
                        'discount' => $finalDiscountAmount,
                        'tax' => $finalTaxAmount,
                        'total' => $total,
                        'paid' => $paid,
                        'change' => (float) $this->change,
                        'payment_method' => $this->paymentMethod,
                        'order_type' => $this->orderType,
                        'table_number' => $this->orderType === 'dine_in' ? ($this->tableNumber ?: null) : null,
                        'customer_name' => $this->customerName ?: null,
                        'status' => 'completed',
                    ]);
                }
            } else {
                // Checkout transaksi langsung baru
                $transaction = Transaction::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $shiftId,
                    'subtotal' => (float) $this->subtotal,
                    'discount' => $finalDiscountAmount,
                    'tax' => $finalTaxAmount,
                    'total' => $total,
                    'paid' => $paid,
                    'change' => (float) $this->change,
                    'payment_method' => $this->paymentMethod,
                    'order_type' => $this->orderType,
                    'table_number' => $this->orderType === 'dine_in' ? ($this->tableNumber ?: null) : null,
                    'customer_name' => $this->customerName ?: null,
                    'status' => 'completed',
                ]);
            }

            foreach ($this->cart as $item) {
                $harga_beli = $item['harga_beli'] ?? 0;
                $profit = ($item['price'] - $harga_beli) * $item['quantity'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'harga_beli' => $harga_beli,
                    'subtotal' => $item['subtotal'],
                    'profit' => $profit,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            if ($this->activeShift) {
                $this->activeShift->recalculateTotals();
            }

            $this->lastTransaction = Transaction::with(['details.product', 'user'])->find($transaction->id);
            $this->lastInvoice = $transaction->invoice_number ?? '';

            $setting = Setting::first();
            $autoPrintReceipt = (bool) ($setting->auto_print_receipt ?? true);
            $autoPrintKitchen = (bool) ($setting->auto_print_kitchen ?? false);

            $this->showPaymentModal = false;
            $this->showMobileCart = false;
            $this->showSuccessModal = true;
            $this->dispatch('transaction-completed', 
                invoice: $this->lastInvoice,
                autoPrintReceipt: $autoPrintReceipt,
                autoPrintKitchen: $autoPrintKitchen
            );
            $this->resetTransaction();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->notify('error', 'Error: ' . $e->getMessage());
        }
    }

    public function openAvailabilityModal()
    {
        $this->showAvailabilityModal = true;
        $this->availabilitySearch = '';
        $this->availabilityCategoryFilter = '';
    }

    public function closeAvailabilityModal()
    {
        $this->showAvailabilityModal = false;
        $this->loadProducts();
    }

    public function setAvailabilityTab($tab)
    {
        $this->availabilityTab = $tab;
    }

    public function toggleProductAvailability($productId)
    {
        $product = Product::find($productId);
        if ($product) {
            $product->is_active = !$product->is_active;
            $product->save();
            $this->loadProducts();
            $statusLabel = $product->is_active ? 'Diaktifkan (Tersedia di Kasir)' : 'Dinonaktifkan (Habis / Kosong)';
            $this->notify($product->is_active ? 'success' : 'info', "Menu '{$product->name}' berhasil {$statusLabel}.");
        }
    }

    public function toggleCategoryAvailability($categoryId)
    {
        $category = Category::find($categoryId);
        if ($category) {
            $category->is_active = !$category->is_active;
            $category->save();
            $this->loadProducts();
            $statusLabel = $category->is_active ? 'Diaktifkan (Tersedia di Kasir)' : 'Dinonaktifkan (Disembunyikan dari POS)';
            $this->notify($category->is_active ? 'success' : 'info', "Kategori '{$category->name}' berhasil {$statusLabel}.");
        }
    }

    public function render()
    {
        $categories = Category::where('is_active', true)->withCount('products')->orderBy('name', 'asc')->get();
        $allCategories = Category::withCount('products')->orderBy('name', 'asc')->get();

        $openBillsCount = Transaction::where('status', 'pending')->count();
        $openBills = [];
        if ($this->showOpenBillsModal) {
            $query = Transaction::with(['details.product', 'user'])
                ->where('status', 'pending')
                ->latest();

            if ($this->openBillsSearch) {
                $query->where(function ($q) {
                    $q->where('table_number', 'like', '%' . $this->openBillsSearch . '%')
                      ->orWhere('customer_name', 'like', '%' . $this->openBillsSearch . '%')
                      ->orWhere('invoice_number', 'like', '%' . $this->openBillsSearch . '%');
                });
            }
            $openBills = $query->get();
        }

        $availabilityProducts = [];
        if ($this->showAvailabilityModal) {
            $query = Product::with('category')->latest();
            if ($this->availabilitySearch) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->availabilitySearch . '%')
                      ->orWhere('sku', 'like', '%' . $this->availabilitySearch . '%');
                });
            }
            if ($this->availabilityCategoryFilter) {
                $query->where('category_id', $this->availabilityCategoryFilter);
            }
            $availabilityProducts = $query->get();
        }

        $occupiedTables = Transaction::where('status', 'pending')
            ->whereNotNull('table_number')
            ->pluck('table_number')
            ->map(fn($t) => (string) $t)
            ->toArray();

        return view('livewire.pos.pos-index', [
            'categories' => $categories,
            'allCategories' => $allCategories,
            'openBillsCount' => $openBillsCount,
            'openBills' => $openBills,
            'occupiedTables' => $occupiedTables,
            'availabilityProducts' => $availabilityProducts,
        ]);
    }
}