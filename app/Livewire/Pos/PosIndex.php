<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;
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
    public $lastInvoice = '';
    public $lastTransaction = null;

    public function mount()
    {
        $this->loadProducts();
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
        // For Cafe Mode: display all menu items without hiding when stock reaches 0
        $query = Product::with('category');

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
            ->orderByDesc('total_sold')
            ->orderBy('name', 'asc');

        $this->products = $query->get();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            $this->notify('error', 'Menu tidak ditemukan.');
            return;
        }

        // Cek apakah produk sudah ada di keranjang (tanpa notes khusus)
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
            $this->calculateTotal();
            $this->notify('success', 'Masuk keranjang: ' . $product->name);
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'category_name' => $product->category->name ?? '',
                'price' => $product->price,
                'harga_beli' => $product->harga_beli,
                'quantity' => 1,
                'subtotal' => $product->price,
                'stock' => $product->stock,
                'notes' => ''
            ];
            $this->calculateTotal();
            $this->notify('success', 'Masuk keranjang: ' . $product->name);
        }
    }

    public function scanBarcode()
    {
        $barcode = trim($this->search);

        if (empty($barcode)) {
            return;
        }

        $product = Product::where('barcode', $barcode)->first();

        if ($product) {
            $this->addToCart($product->id);
            $this->search = '';
            $this->loadProducts();
        } else {
            $this->notify('info', 'Barcode tidak ditemukan. Lanjut cari nama menu.');
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
                $notesParts[] = "Sugar: " . $this->tempSugarLevel;
            }
            if ($this->tempIceLevel !== 'Normal') {
                $notesParts[] = "Ice: " . $this->tempIceLevel;
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

        if ($this->orderType === 'dine_in' && empty(trim($this->tableNumber))) {
            $this->notify('error', 'Silakan masukkan Nomor Meja untuk Dine In!');
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

    public function processPayment()
    {
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

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
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

                Product::find($item['id'])->decrement('stock', $item['quantity']);

                \App\Models\StockMovement::create([
                    'product_id' => $item['id'],
                    'quantity' => -$item['quantity'],
                    'type' => 'out',
                    'notes' => 'Penjualan POS: ' . $transaction->invoice_number
                ]);
            }

            DB::commit();

            $this->lastTransaction = Transaction::with(['details.product', 'user'])->find($transaction->id);
            $this->lastInvoice = $transaction->invoice_number ?? '';

            $this->showPaymentModal = false;
            $this->showMobileCart = false;
            $this->showSuccessModal = true;
            $this->resetTransaction();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->notify('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $categories = Category::withCount('products')->orderBy('name', 'asc')->get();

        return view('livewire.pos.pos-index', [
            'categories' => $categories,
        ]);
    }
}