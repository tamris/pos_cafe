<?php

namespace App\Livewire\Categories;

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Kategori Menu - Cafe Noli')]
class CategoryIndex extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $categoryId;
    public $name = '';
    public $description = '';
    public $isEdit = false;
    public $showModal = false;

    protected $rules = [
        'name' => 'required|min:3',
        'description' => 'nullable'
    ];

    protected $messages = [
        'name.required' => 'Nama kategori wajib diisi',
        'name.min' => 'Nama kategori minimal 3 karakter',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->resetForm();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'categoryId', 'isEdit']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        if ($this->isEdit) {
            $category = Category::find($this->categoryId);
            $category->update([
                'name' => $this->name,
                'description' => $this->description
            ]);
            session()->flash('message', 'Kategori berhasil diupdate');
        } else {
            Category::create([
                'name' => $this->name,
                'description' => $this->description
            ]);
            session()->flash('message', 'Kategori berhasil ditambahkan');
        }

        $this->closeModal();
    }

    public function edit($id)
    {
        $category = Category::find($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        try {
            Category::find($id)->delete();
            session()->flash('message', 'Kategori berhasil dihapus');
        } catch (\Exception $e) {
            session()->flash('error', 'Kategori tidak dapat dihapus karena masih memiliki produk');
        }
    }

    public function render()
    {
        $categories = Category::where('name', 'like', '%' . $this->search . '%')
            ->withCount('products')
            ->latest()
            ->paginate(10);

        return view('livewire.categories.category-index', [
            'categories' => $categories
        ]);
    }
}