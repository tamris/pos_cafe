<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Layout('components.layouts.customer')]
#[Title('Noli Coffee & Space - Online Menu & Space')]
class CafeLanding extends Component
{
    #[Url(as: 'table')]
    public $tableParam = '';

    public $tableNumber = '';
    public $selectedCategory = 'all';
    public $search = '';

    // Quick View Modal
    public $previewProduct = null;
    public $showQuickView = false;

    public function mount()
    {
        if (!empty($this->tableParam)) {
            $param = trim($this->tableParam);
            if (is_numeric($param) && (int) $param > 0) {
                $this->tableNumber = sprintf('%02d', (int) $param);
            } else {
                $this->tableNumber = $param;
            }
        }
    }

    public function selectCategory($cat)
    {
        $this->selectedCategory = $cat;
    }

    public function openQuickView($productId)
    {
        $product = Product::with('category')->find($productId);
        if ($product) {
            $this->previewProduct = $product;
            $this->showQuickView = true;
        }
    }

    public function closeQuickView()
    {
        $this->showQuickView = false;
        $this->previewProduct = null;
    }

    public function render()
    {
        $setting = Setting::first();

        // Check Store Operating Status
        $isStoreOpen = true;
        $isOnlineOrderActive = true;
        if ($setting) {
            if (isset($setting->is_store_open)) {
                $isStoreOpen = (bool) $setting->is_store_open;
            }
            if (isset($setting->is_online_order_active)) {
                $isOnlineOrderActive = (bool) $setting->is_online_order_active;
            }
        }

        // Active Categories
        $categoriesQuery = Category::query();
        if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'is_active')) {
            $categoriesQuery->where('is_active', true);
        }
        $categories = $categoriesQuery->withCount(['products' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        // Products Query
        $productsQuery = Product::query()
            ->where('is_active', true)
            ->with('category');

        if ($this->selectedCategory !== 'all') {
            if (is_numeric($this->selectedCategory)) {
                $productsQuery->where('category_id', $this->selectedCategory);
            } else {
                $catName = strtolower($this->selectedCategory);
                $productsQuery->whereHas('category', function ($q) use ($catName) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$catName}%"]);
                });
            }
        }

        if (!empty($this->search)) {
            $term = '%' . strtolower($this->search) . '%';
            $productsQuery->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            });
        }

        $products = $productsQuery->orderBy('id', 'asc')->get();

        // 1. Fetch Top Coffee Products (Juara Kategori Kopi)
        $topCoffee = Product::where('is_active', true)
            ->whereNotNull('image')
            ->whereHas('category', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%Espresso%')
                        ->orWhere('name', 'like', '%Coffee%');
                })->where('name', 'not like', '%Non-Coffee%');
            })
            ->with('category')
            ->withSum(['transactionDetails as total_sold' => function ($q) {
                $q->whereHas('transaction', function ($t) {
                    $t->where('status', 'completed');
                });
            }], 'quantity')
            ->orderByDesc('total_sold')
            ->orderBy('price', 'desc')
            ->take(3)
            ->get();

        // 2. Fetch Top Non-Coffee Product (Juara Kategori Non-Kopi)
        $topNonCoffee = Product::where('is_active', true)
            ->whereNotNull('image')
            ->whereHas('category', function ($q) {
                $q->where('name', 'like', '%Non-Coffee%')
                  ->orWhere('name', 'like', '%Tea%');
            })
            ->with('category')
            ->withSum(['transactionDetails as total_sold' => function ($q) {
                $q->whereHas('transaction', function ($t) {
                    $t->where('status', 'completed');
                });
            }], 'quantity')
            ->orderByDesc('total_sold')
            ->orderBy('price', 'desc')
            ->take(1)
            ->get();

        // Assign Realistic Badges (Option 1: Coffee vs Non-Coffee Champion)
        $signaturesList = collect();

        // #1 Coffee -> Best Seller
        if ($topCoffee->isNotEmpty()) {
            $c1 = $topCoffee->get(0);
            $c1->badge_label = 'Best Seller';
            $c1->badge_bg = 'bg-[#b88646]';
            $c1->badge_border = 'border border-amber-200/40';
            $c1->badge_icon = 'fas fa-star text-amber-100';
            $signaturesList->push($c1);
        }

        // #1 Non-Coffee -> Most Popular
        if ($topNonCoffee->isNotEmpty()) {
            $nc1 = $topNonCoffee->get(0);
            $nc1->badge_label = 'Most Popular';
            $nc1->badge_bg = 'bg-[#784421]';
            $nc1->badge_border = 'border border-amber-100/30';
            $nc1->badge_icon = 'fas fa-thumbs-up text-amber-100';
            $signaturesList->push($nc1);
        }

        // #2 Coffee -> Top Ordered
        if ($topCoffee->count() > 1) {
            $c2 = $topCoffee->get(1);
            $c2->badge_label = 'Top Ordered';
            $c2->badge_bg = 'bg-[#0e382c]';
            $c2->badge_border = 'border border-emerald-400/30';
            $c2->badge_icon = 'fas fa-award text-emerald-300';
            $signaturesList->push($c2);
        }

        // #3 Coffee -> Top Ordered
        if ($topCoffee->count() > 2) {
            $c3 = $topCoffee->get(2);
            $c3->badge_label = 'Top Ordered';
            $c3->badge_bg = 'bg-[#0e382c]';
            $c3->badge_border = 'border border-emerald-400/30';
            $c3->badge_icon = 'fas fa-award text-emerald-300';
            $signaturesList->push($c3);
        }

        // Fallback if no specific Coffee/Non-Coffee categories matched
        if ($signaturesList->isEmpty()) {
            $fallbackProducts = Product::where('is_active', true)->with('category')->take(4)->get();
            foreach ($fallbackProducts as $idx => $fp) {
                $fp->badge_label = $idx === 0 ? 'Best Seller' : 'Menu Favorit';
                $fp->badge_bg = $idx === 0 ? 'bg-[#b88646]' : 'bg-[#0e382c]';
                $fp->badge_border = $idx === 0 ? 'border border-amber-200/40' : 'border border-emerald-400/30';
                $fp->badge_icon = $idx === 0 ? 'fas fa-star text-amber-100' : 'fas fa-award text-emerald-300';
                $signaturesList->push($fp);
            }
        }

        $bestSellers = $signaturesList;

        // Dynamic Sales Stats with Smart Milestone Rounding (e.g. 50+, 60+, 100+, 150+, 1.2k+)
        $totalSold = (int) \App\Models\TransactionDetail::whereHas('transaction', function ($q) {
            $q->where('status', 'completed');
        })->sum('quantity');

        if ($totalSold >= 1000) {
            $thousands = floor($totalSold / 100) / 10;
            $soldFormatted = ($thousands == floor($thousands) ? (int)$thousands : number_format($thousands, 1)) . 'k+';
        } elseif ($totalSold >= 100) {
            // Milestone kelipatan 50 (100+, 150+, 200+, dst)
            $soldFormatted = (floor($totalSold / 50) * 50) . '+';
        } elseif ($totalSold >= 10) {
            // Milestone kelipatan 10 (10+, 20+, ... 50+, 60+, dst)
            $soldFormatted = (floor($totalSold / 10) * 10) . '+';
        } else {
            $soldFormatted = '10+';
        }

        return view('livewire.customer.cafe-landing', [
            'setting' => $setting,
            'isStoreOpen' => $isStoreOpen,
            'isOnlineOrderActive' => $isOnlineOrderActive,
            'categories' => $categories,
            'products' => $products,
            'signatures' => $bestSellers,
            'bestSellers' => $bestSellers,
            'totalSold' => $totalSold,
            'soldFormatted' => $soldFormatted,
        ]);
    }
}