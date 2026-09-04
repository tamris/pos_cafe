<?php

namespace App\Livewire\Addons;

use Livewire\Component;
use App\Models\Addon;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Add-ons & Topping - POS Cafe')]
class AddonIndex extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = 'all'; // 'all', 'active', 'inactive', 'trashed'
    public $addonId;
    public $name = '';
    public $price = 0;
    public $formattedPrice = '0';
    public $harga_beli = 0;
    public $formattedHargaBeli = '0';
    public $is_active = true;
    public $selectedCategories = [];
    public $isEdit = false;
    public $showModal = false;

    protected function rules()
    {
        return [
            'name' => 'required|min:2|max:100',
            'price' => 'required|numeric|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'selectedCategories' => 'nullable|array',
            'selectedCategories.*' => 'exists:categories,id',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama add-on wajib diisi.',
        'name.min' => 'Nama add-on minimal 2 karakter.',
        'price.required' => 'Harga jual add-on wajib diisi.',
        'price.min' => 'Harga jual tidak boleh negatif.',
        'harga_beli.required' => 'HPP / modal add-on wajib diisi.',
        'harga_beli.min' => 'HPP tidak boleh negatif.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
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

    public function updatedFormattedPrice($value)
    {
        $clean = preg_replace('/[^0-9]/', '', (string)$value);
        $this->price = (float)($clean ?: 0);
        $this->formattedPrice = number_format($this->price, 0, ',', '.');
    }

    public function updatedFormattedHargaBeli($value)
    {
        $clean = preg_replace('/[^0-9]/', '', (string)$value);
        $this->harga_beli = (float)($clean ?: 0);
        $this->formattedHargaBeli = number_format($this->harga_beli, 0, ',', '.');
    }

    public function toggleStatus($id)
    {
        $addon = Addon::find($id);
        if ($addon) {
            $addon->is_active = !$addon->is_active;
            $addon->save();
            $label = $addon->is_active ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('message', "Add-on '{$addon->name}' berhasil {$label}.");
        }
    }

    public function selectAllCategories()
    {
        $this->selectedCategories = Category::pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    public function clearAllCategories()
    {
        $this->selectedCategories = [];
    }

    public function openModal()
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses dibatasi. Hanya Administrator yang dapat mengelola add-on.');
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
        $this->addonId = null;
        $this->name = '';
        $this->price = 0;
        $this->formattedPrice = '0';
        $this->harga_beli = 0;
        $this->formattedHargaBeli = '0';
        $this->is_active = true;
        $this->selectedCategories = [];
        $this->isEdit = false;
        $this->resetValidation();
    }

    public function store()
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses ditolak.');
            return;
        }

        $this->validate();

        $addon = Addon::create([
            'name' => trim($this->name),
            'price' => (float)$this->price,
            'harga_beli' => (float)$this->harga_beli,
            'is_active' => (bool)$this->is_active,
        ]);

        if (!empty($this->selectedCategories)) {
            $addon->categories()->sync($this->selectedCategories);
        }

        session()->flash('message', "Add-on '{$addon->name}' berhasil ditambahkan.");
        $this->closeModal();
    }

    public function edit($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses ditolak.');
            return;
        }

        $addon = Addon::with('categories')->findOrFail($id);
        $this->addonId = $addon->id;
        $this->name = $addon->name;
        $this->price = (float)$addon->price;
        $this->formattedPrice = number_format($this->price, 0, ',', '.');
        $this->harga_beli = (float)$addon->harga_beli;
        $this->formattedHargaBeli = number_format($this->harga_beli, 0, ',', '.');
        $this->is_active = (bool)$addon->is_active;
        $this->selectedCategories = $addon->categories->pluck('id')->map(fn($id) => (string)$id)->toArray();

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function update()
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses ditolak.');
            return;
        }

        $this->validate();

        $addon = Addon::findOrFail($this->addonId);
        $addon->update([
            'name' => trim($this->name),
            'price' => (float)$this->price,
            'harga_beli' => (float)$this->harga_beli,
            'is_active' => (bool)$this->is_active,
        ]);

        $addon->categories()->sync($this->selectedCategories ?? []);

        session()->flash('message', "Add-on '{$addon->name}' berhasil diperbarui.");
        $this->closeModal();
    }

    public function delete($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses ditolak.');
            return;
        }

        $addon = Addon::find($id);
        if ($addon) {
            $addon->delete();
            session()->flash('message', "Add-on '{$addon->name}' berhasil dipindahkan ke sampah.");
        }
    }

    public function restore($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses ditolak.');
            return;
        }

        $addon = Addon::onlyTrashed()->find($id);
        if ($addon) {
            $addon->restore();
            session()->flash('message', "Add-on '{$addon->name}' berhasil dipulihkan.");
        }
    }

    public function forceDelete($id)
    {
        if (auth()->user()->role !== 'admin') {
            session()->flash('error', 'Akses ditolak.');
            return;
        }

        $addon = Addon::onlyTrashed()->find($id);
        if ($addon) {
            $addon->categories()->detach();
            $addon->forceDelete();
            session()->flash('message', "Add-on berhasil dihapus permanen.");
        }
    }

    public function render()
    {
        $query = Addon::with('categories');

        // Status Filter
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        } elseif ($this->statusFilter === 'trashed') {
            $query->onlyTrashed();
        }

        // Category Filter
        if (!empty($this->categoryFilter)) {
            $query->whereHas('categories', function($q) {
                $q->where('categories.id', $this->categoryFilter);
            });
        }

        // Search Filter
        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $addons = $query->latest()->paginate(10);
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('livewire.addons.addon-index', [
            'addons' => $addons,
            'categories' => $categories,
            'counts' => [
                'all' => Addon::count(),
                'active' => Addon::where('is_active', true)->count(),
                'inactive' => Addon::where('is_active', false)->count(),
                'trashed' => Addon::onlyTrashed()->count(),
            ],
        ]);
    }
}
