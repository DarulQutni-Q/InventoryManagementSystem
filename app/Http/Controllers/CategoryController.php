<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Category::withCount('products');

        if ($search = trim($request->input('search', ''))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ], [
            'name.unique' => 'Nama kategori sudah digunakan.',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        $this->authorizeAdmin();

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories')->ignore($category->id)],
        ], [
            'name.unique' => 'Nama kategori sudah digunakan.',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeAdmin();

        // Proteksi integritas relasional: dilarang menghapus kategori yang masih memiliki produk aktif
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memuat produk aktif.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

}
