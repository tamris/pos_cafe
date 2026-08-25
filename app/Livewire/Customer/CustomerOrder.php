<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Layout('components.layouts.customer')]
#[Title('Pesan Menu - Self Order')]
class CustomerOrder extends Component
{
    #[Url(as: 'table')]
    public $tableParam = '';

    #[Url(as: 'type')]
    public $typeParam = '';

    // Customer Identity State
    public $orderType = 'dine_in'; // 'dine_in' or 'take_away'
    public $tableNumber = '';
    public $customerName = '';
    public $customerPhone = '';
    public $showIdentityModal = false;

    // Menu Navigation State
    public $selectedCategory = '';
    public $search = '';

    // Cart State
    public $cart = [];
    public $subtotal = 0;
    public $taxRate = 0; // percentage
    public $taxAmount = 0;
    public $total = 0;

    // Item Customization Modal
    public $showCustomizeModal = false;
    public $selectedProduct = null;
    public $editingCartKey = null;
    public $itemNotes = '';
    public $drinkType = 'Ice'; // 'Ice' or 'Hot'
    public $sugarLevel = 'Normal';
    public $iceLevel = 'Normal';
    public $modalQty = 1;

    // Review / Checkout Drawer
    public $showCartDrawer = false;

    // Order History Modal
    public $showHistoryModal = false;
    public $recentTokens = [];

    public function mount()
    {
        // Populate from URL query parameters if present
        if (!empty($this->tableParam)) {
            $param = trim($this->tableParam);
            if (is_numeric($param) && (int) $param > 0) {
                $this->tableNumber = sprintf('%02d', (int) $param);
            } else {
                $this->tableNumber = $param;
            }
            $this->orderType = 'dine_in';
        }

        if (!empty($this->typeParam) && in_array($this->typeParam, ['dine_in', 'take_away'])) {
            $this->orderType = $this->typeParam;
        }

        // Get Tax Rate from Setting if exists
        $setting = Setting::first();
        if ($setting && isset($setting->tax_percentage)) {
            $this->taxRate = (float) $setting->tax_percentage;
        }
    }

    public function updatedTableNumber($val)
    {
        $clean = preg_replace('/\D/', '', (string) $val);
        if (!empty($clean)) {
            $this->tableNumber = sprintf('%02d', (int) $clean);
        } else {
            $this->tableNumber = '';
        }
    }

    public function setOrderType($type)
    {
        $this->orderType = $type;
        if ($type === 'take_away') {
            $this->tableNumber = '';
        }
    }

    public function selectCategory($categoryId = '')
    {
        $this->selectedCategory = $categoryId;
    }

    public function clearCategory()
    {
        $this->selectedCategory = '';
    }

    public function openIdentityModal()
    {
        $this->showCartDrawer = false;
        $this->showIdentityModal = true;
    }

    public function closeIdentityModal()
    {
        $this->showIdentityModal = false;
        if (!empty($this->cart)) {
            $this->showCartDrawer = true;
        }
    }

    public function syncTokens($tokens)
    {
        if (is_array($tokens)) {
            $this->recentTokens = array_slice(array_unique(array_filter($tokens)), 0, 25);
            session(['self_order_tokens' => array_unique(array_merge((array) session('self_order_tokens', []), $this->recentTokens))]);
        }
    }

    public function openHistoryModal()
    {
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
    }

    public function getHistoryOrdersProperty()
    {
        $tokens = array_unique(array_merge(
            (array) session('self_order_tokens', []),
            (array) $this->recentTokens
        ));

        if (empty($tokens)) {
            return collect();
        }

        return Transaction::with(['details.product'])
            ->whereIn('order_token', $tokens)
            ->latest()
            ->take(15)
            ->get();
    }

    public function getActiveOrdersProperty()
    {
        return $this->historyOrders->filter(function($order) {
            return in_array($order->status, ['pending', 'processing', 'ready']) && $order->payment_status === 'paid';
        });
    }

    public function openCustomizeModal($productId)
    {
        $product = Product::active()
            ->whereHas('category', function ($q) {
                $q->where('is_active', true);
            })
            ->find($productId);

        if (!$product) {
            $this->dispatch('alert', type: 'error', message: 'Menu tidak ditemukan atau sedang tidak aktif.');
            return;
        }

        $this->editingCartKey = null;
        $this->selectedProduct = $product;
        $this->itemNotes = '';
        $this->drinkType = 'Ice';
        $this->sugarLevel = 'Normal';
        $this->iceLevel = 'Normal';
        $this->modalQty = 1;
        $this->showCustomizeModal = true;
    }

    public function editCartItem($cartKey)
    {
        if (!isset($this->cart[$cartKey])) {
            return;
        }

        $item = $this->cart[$cartKey];
        $product = Product::find($item['id']);

        if (!$product) {
            $this->dispatch('alert', type: 'error', message: 'Menu tidak ditemukan.');
            return;
        }

        $this->editingCartKey = $cartKey;
        $this->selectedProduct = $product;
        $this->modalQty = $item['quantity'] ?? 1;

        // Parse existing notes
        $notes = $item['notes'] ?? '';
        $this->drinkType = str_contains($notes, 'Hot') ? 'Hot' : 'Ice';
        
        if (str_contains($notes, 'Less Sugar')) {
            $this->sugarLevel = 'Less Sugar';
        } elseif (str_contains($notes, 'No Sugar')) {
            $this->sugarLevel = 'No Sugar';
        } else {
            $this->sugarLevel = 'Normal';
        }

        if (str_contains($notes, 'Less Ice')) {
            $this->iceLevel = 'Less Ice';
        } elseif (str_contains($notes, 'No Ice') || str_contains($notes, 'Tanpa Es')) {
            $this->iceLevel = 'No Ice';
        } else {
            $this->iceLevel = 'Normal';
        }

        // Clean extra custom notes if any
        $parts = array_filter(preg_split('/[|•]/', $notes), function($p) {
            $t = trim($p);
            return !in_array($t, ['Ice', 'Hot', 'Less Sugar', 'No Sugar', 'Normal', 'Less Ice', 'No Ice', 'Ice (Tanpa Es)', 'Ice (No Ice)', 'Ice (Less Ice)']);
        });
        $this->itemNotes = trim(implode(' • ', $parts));

        // Hide Cart Drawer while editing item so there is no visual overlap
        $this->showCartDrawer = false;
        $this->showCustomizeModal = true;
    }

    public function closeCustomizeModal()
    {
        $wasEditing = !empty($this->editingCartKey);
        $this->showCustomizeModal = false;
        $this->selectedProduct = null;
        $this->editingCartKey = null;

        if ($wasEditing) {
            $this->showCartDrawer = true;
        }
    }

    public function incrementModalQty()
    {
        $this->modalQty++;
    }

    public function decrementModalQty()
    {
        if ($this->modalQty > 1) {
            $this->modalQty--;
        }
    }

    public function addConfiguredToCart()
    {
        if (!$this->selectedProduct) return;

        $notesParts = [];
        $categoryName = strtolower($this->selectedProduct->category?->name ?? '');
        $isBeverage = str_contains($categoryName, 'coffee') || 
                      str_contains($categoryName, 'tea') || 
                      str_contains($categoryName, 'minuman') || 
                      str_contains($categoryName, 'drink');

        if ($isBeverage) {
            if ($this->drinkType === 'Hot') {
                $notesParts[] = 'Hot';
            } else {
                if ($this->iceLevel === 'No Ice') {
                    $notesParts[] = 'Ice (Tanpa Es)';
                } elseif ($this->iceLevel === 'Less Ice') {
                    $notesParts[] = 'Ice (Less Ice)';
                } else {
                    $notesParts[] = 'Ice';
                }
            }

            if ($this->sugarLevel === 'Less Sugar') {
                $notesParts[] = 'Less Sugar';
            } elseif ($this->sugarLevel === 'No Sugar') {
                $notesParts[] = 'No Sugar';
            }
        }

        if (!empty(trim($this->itemNotes))) {
            $notesParts[] = trim($this->itemNotes);
        }

        $finalNotes = implode(' • ', $notesParts);
        $cartKey = $this->selectedProduct->id . '-' . md5($finalNotes);

        if ($this->editingCartKey && $this->editingCartKey !== $cartKey) {
            unset($this->cart[$this->editingCartKey]);
        }

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity'] = $this->editingCartKey ? $this->modalQty : ($this->cart[$cartKey]['quantity'] + $this->modalQty);
            $this->cart[$cartKey]['subtotal'] = $this->cart[$cartKey]['quantity'] * $this->cart[$cartKey]['price'];
            $this->cart[$cartKey]['notes'] = $finalNotes;
        } else {
            $this->cart[$cartKey] = [
                'cart_key' => $cartKey,
                'id' => $this->selectedProduct->id,
                'name' => $this->selectedProduct->name,
                'price' => (float) $this->selectedProduct->price,
                'harga_beli' => (float) ($this->selectedProduct->harga_beli ?? 0),
                'quantity' => $this->modalQty,
                'subtotal' => (float) $this->selectedProduct->price * $this->modalQty,
                'image' => $this->selectedProduct->image,
                'notes' => $finalNotes,
                'category_name' => $this->selectedProduct->category?->name ?? '',
            ];
        }

        $wasEditing = !empty($this->editingCartKey);
        $this->calculateTotals();
        $this->closeCustomizeModal();

        if ($wasEditing) {
            $this->showCartDrawer = true;
        }
    }

    public function quickAddToCart($productId)
    {
        $product = Product::active()
            ->whereHas('category', function ($q) {
                $q->where('is_active', true);
            })
            ->find($productId);

        if (!$product) return;

        $categoryName = strtolower($product->category?->name ?? '');
        $isBeverage = str_contains($categoryName, 'coffee') || 
                      str_contains($categoryName, 'tea') || 
                      str_contains($categoryName, 'minuman') || 
                      str_contains($categoryName, 'drink');

        // If it's a beverage with options, open customize modal instead
        if ($isBeverage) {
            $this->openCustomizeModal($productId);
            return;
        }

        $cartKey = $product->id . '-' . md5('');

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity']++;
            $this->cart[$cartKey]['subtotal'] = $this->cart[$cartKey]['quantity'] * $this->cart[$cartKey]['price'];
        } else {
            $this->cart[$cartKey] = [
                'cart_key' => $cartKey,
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'harga_beli' => (float) ($product->harga_beli ?? 0),
                'quantity' => 1,
                'subtotal' => (float) $product->price,
                'image' => $product->image,
                'notes' => '',
                'category_name' => $product->category?->name ?? '',
            ];
        }

        $this->calculateTotals();
    }

    public function updateQuantity($cartKey, $action)
    {
        if (!isset($this->cart[$cartKey])) return;

        if ($action === 'increase') {
            $this->cart[$cartKey]['quantity']++;
        } elseif ($action === 'decrease') {
            $this->cart[$cartKey]['quantity']--;
            if ($this->cart[$cartKey]['quantity'] <= 0) {
                unset($this->cart[$cartKey]);
            }
        }

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['subtotal'] = $this->cart[$cartKey]['quantity'] * $this->cart[$cartKey]['price'];
        }

        $this->calculateTotals();
    }

    public function removeFromCart($cartKey)
    {
        unset($this->cart[$cartKey]);
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = array_sum(array_column($this->cart, 'subtotal'));
        $this->taxAmount = ($this->subtotal * $this->taxRate) / 100;
        $this->total = $this->subtotal + $this->taxAmount;
    }

    public function openCartDrawer()
    {
        if (empty($this->cart)) return;
        $this->showCartDrawer = true;
    }

    public function closeCartDrawer()
    {
        $this->showCartDrawer = false;
    }

    public function proceedToCheckout()
    {
        if (empty($this->cart)) {
            $this->dispatch('alert', type: 'error', message: 'Keranjang belanja masih kosong.');
            return;
        }

        // Check if cafe is currently open (at least 1 cashier shift is active)
        $activeShift = \App\Models\CashierShift::where('status', 'open')->latest()->first();
        if (!$activeShift) {
            $this->dispatch('alert', type: 'error', message: 'Mohon maaf, saat ini kafe belum menerima pesanan. Pemesanan dapat dilakukan saat jam operasional buka.');
            return;
        }

        // Check if store paused online orders (rush mode)
        $setting = Setting::first();
        if ($setting && !$setting->is_online_order_active) {
            $this->dispatch('alert', type: 'warning', message: 'Mohon maaf, saat ini antrean dapur kafe sedang sangat padat dan penerimaan pesanan baru sedang dijeda sementara.');
            return;
        }

        // Check if customer identity is complete
        if (empty(trim($this->customerName)) || ($this->orderType === 'dine_in' && empty(trim($this->tableNumber)))) {
            $this->openIdentityModal();
            $this->dispatch('alert', type: 'warning', message: 'Mohon lengkapi nama pemesan ' . ($this->orderType === 'dine_in' ? 'dan nomor meja' : '') . ' terlebih dahulu.');
            return;
        }

        // Validate customer identity
        $rules = [
            'customerName' => 'required|min:2|max:50',
            'orderType' => 'required|in:dine_in,take_away',
        ];

        if ($this->orderType === 'dine_in') {
            $rules['tableNumber'] = ['required', 'regex:/^[0-9]+$/', 'max:5'];
        }

        $this->validate($rules, [
            'customerName.required' => 'Mohon isi nama pemesan terlebih dahulu.',
            'customerName.min' => 'Nama pemesan minimal 2 karakter.',
            'tableNumber.required' => 'Mohon isi nomor meja untuk pesanan Makan di Tempat (Dine In).',
            'tableNumber.regex' => 'Nomor meja harus berupa angka (contoh: 01, 02).',
        ]);

        if ($this->orderType === 'dine_in') {
            $clean = preg_replace('/\D/', '', (string) $this->tableNumber);
            $this->tableNumber = !empty($clean) ? sprintf('%02d', (int) $clean) : null;
        }

        DB::beginTransaction();
        try {
            // Create Transaction as self-order linked to the active cashier shift
            $transaction = Transaction::create([
                'shift_id' => $activeShift->id,
                'order_source' => 'self_order',
                'order_type' => $this->orderType,
                'table_number' => $this->orderType === 'dine_in' ? $this->tableNumber : null,
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone ?: null,
                'subtotal' => (float) $this->subtotal,
                'discount' => 0,
                'tax' => (float) $this->taxAmount,
                'total' => (float) $this->total,
                'paid' => 0,
                'change' => 0,
                'payment_method' => 'qris',
                'payment_status' => 'unpaid',
                'status' => 'pending',
            ]);

            foreach ($this->cart as $item) {
                $hargaBeli = $item['harga_beli'] ?? 0;
                $profit = ($item['price'] - $hargaBeli) * $item['quantity'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $item['subtotal'],
                    'profit' => $profit,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            // Track recent token in session
            session()->push('self_order_tokens', $transaction->order_token);

            // Redirect to Payment Screen
            $this->redirect(route('customer.payment', ['token' => $transaction->order_token]), navigate: false);
            return;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', type: 'error', message: 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $setting = Setting::first();
        $activeShift = \App\Models\CashierShift::where('status', 'open')->latest()->first();
        $isStoreOpen = (bool) $activeShift;

        $categories = Category::where('is_active', true)
            ->whereHas('products', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['products' => function ($q) {
                $q->where('is_active', true)->orderBy('name', 'asc');
            }])
            ->orderBy('name', 'asc')
            ->get();

        $query = Product::active()
            ->whereHas('category', function ($q) {
                $q->where('is_active', true);
            })
            ->with('category');

        if (!empty($this->selectedCategory)) {
            $query->where('category_id', $this->selectedCategory);
        }

        if (!empty(trim($this->search))) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . trim($this->search) . '%')
                  ->orWhere('description', 'like', '%' . trim($this->search) . '%');
            });
        }

        $products = $query->orderBy('name', 'asc')->get();
        $totalItemsInCart = array_sum(array_column($this->cart, 'quantity'));

        $isOnlineOrderActive = (bool) ($setting->is_online_order_active ?? true);

        return view('livewire.customer.customer-order', [
            'setting' => $setting,
            'categories' => $categories,
            'products' => $products,
            'totalItemsInCart' => $totalItemsInCart,
            'isStoreOpen' => $isStoreOpen,
            'isOnlineOrderActive' => $isOnlineOrderActive,
            'activeShift' => $activeShift,
        ]);
    }
}
