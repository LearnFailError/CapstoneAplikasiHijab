<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('products')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['slug'] = $this->makeSlug($validated['name']);
        $this->ensureSlugIsAvailable($validated['slug']);
        $category = Category::create($validated);

        return redirect()->route('admin.categories.index')->with('status', "{$category->name} berhasil ditambahkan.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validatedData($request, $category);
        $validated['slug'] = $this->makeSlug($validated['name']);
        $this->ensureSlugIsAvailable($validated['slug'], $category);
        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Kategori tidak bisa dihapus selama masih memiliki produk.',
            ]);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('categories', 'name')->ignore($category?->id),
            ],
        ]);
    }

    private function ensureSlugIsAvailable(string $slug, ?Category $category = null): void
    {
        $query = Category::where('slug', $slug);

        if ($category) {
            $query->whereKeyNot($category->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Nama kategori tersebut menghasilkan slug yang sudah digunakan.',
            ]);
        }
    }

    private function makeSlug(string $name): string
    {
        return Str::slug($name) ?: 'category-'.Str::lower(Str::random(8));
    }
}
