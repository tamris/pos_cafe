<?php

namespace App\Livewire\Categories;

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Kategori Menu - POS Cafe')]
class CategoryIndex extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $statusFilter = 'all'; // 'all', 'active', 'inactive', 'trashed'
    public $categoryId;
    public $name = '';
    public $description = '';
    public $is_active = true;
    public $isEdit = false;
    public $showModal = false;

    protected $rules = [
        'name' => 'required|min:3',
        'description' => 'nullable',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Nama kategori wajib diisi',
        'name.min' => 'Nama kategori minimal 3 karakter',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function setStatusFilter($filter)
    {
        $this->statusFilter = $filter;
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->is_active = !$category->is_active;
            $category->save();
            $statusLabel = $category->is_active ? 'Diaktifkan (Tersedia di POS)' : 'Dinonaktifkan (Disembunyikan dari POS)';
            session()->flash('message', "Kategori '{$category->name}' berhasil {$statusLabel}.");
        }
    }

    public function openModal()
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat menambah kategori.');
            return;
        }
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'categoryId', 'isEdit']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function save()
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses ditolak. Hanya Administrator yang dapat menyimpan kategori.');
            return;
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
        ];

        if ($this->isEdit) {
            $category = Category::withTrashed()->find($this->categoryId);
            if ($category) {
                $category->update($data);
                session()->flash('message', 'Kategori berhasil diupdate');
            }
        } else {
            Category::create($data);
            session()->flash('message', 'Kategori berhasil ditambahkan');
        }

        $this->closeModal();
    }

    public function edit($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat mengedit kategori.');
            return;
        }

        $category = Category::withTrashed()->find($id);
        if (!$category) return;

        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_active = (bool) $category->is_active;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat menghapus kategori.');
            return;
        }

        $category = Category::find($id);
        if (!$category) return;

        // Proteksi: Cek apakah masih memiliki produk aktif
        $activeProductsCount = $category->products()->count();
        if ($activeProductsCount > 0) {
            session()->flash('error', "Kategori '{$category->name}' tidak dapat diarsipkan karena masih memiliki {$activeProductsCount} menu produk aktif. Silakan pindahkan atau nonaktifkan/arsipkan produk terlebih dahulu.");
            return;
        }

        $category->delete();
        session()->flash('message', "Kategori '{$category->name}' berhasil dipindahkan ke Tong Sampah / Arsip.");
    }

    public function restore($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat memulihkan kategori.');
            return;
        }

        $category = Category::onlyTrashed()->find($id);
        if ($category) {
            $category->restore();
            session()->flash('message', "Kategori '{$category->name}' berhasil dipulihkan dari arsip.");
        }
    }

    public function forceDelete($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat menghapus kategori permanen.');
            return;
        }

        $category = Category::onlyTrashed()->find($id);
        if (!$category) return;

        // Proteksi: Cek apakah masih ada produk yang terhubung (termasuk yang diarsipkan)
        $totalProductsCount = $category->products()->withTrashed()->count();
        if ($totalProductsCount > 0) {
            session()->flash('error', "Kategori '{$category->name}' tidak dapat dihapus permanen karena masih memiliki {$totalProductsCount} data produk (aktif/arsip) yang terhubung.");
            return;
        }

        $category->forceDelete();
        session()->flash('message', "Kategori '{$category->name}' berhasil dihapus permanen.");
    }

    public function render()
    {
        $countAll = Category::count();
        $countActive = Category::where('is_active', true)->count();
        $countInactive = Category::where('is_active', false)->count();
        $countTrashed = Category::onlyTrashed()->count();

        if ($this->statusFilter === 'trashed') {
            $query = Category::onlyTrashed();
        } elseif ($this->statusFilter === 'active') {
            $query = Category::where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query = Category::where('is_active', false);
        } else {
            $query = Category::query();
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $categories = $query->withCount('products')
            ->latest()
            ->paginate(10);

        return view('livewire.categories.category-index', [
            'categories' => $categories,
            'countAll' => $countAll,
            'countActive' => $countActive,
            'countInactive' => $countInactive,
            'countTrashed' => $countTrashed,
        ]);
    }
}