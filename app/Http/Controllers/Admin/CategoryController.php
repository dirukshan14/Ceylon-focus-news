<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-categories');
    }

    public function index()
    {
        $categories = Category::with('children')->topLevel()->orderBy('order')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::topLevel()->orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);
        AuditLog::record('category.created', $category);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        $parents = Category::topLevel()->where('id', '!=', $category->id)->orderBy('name')->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            // SECURITY/integrity: a category can't be its own parent (the old 'different:id'
            // rule compared against a field that is never submitted, so it did nothing).
            'parent_id' => ['nullable', 'exists:categories,id', \Illuminate\Validation\Rule::notIn([$category->id])],
            'order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);
        AuditLog::record('category.updated', $category);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->articles()->exists() || $category->children()->exists()) {
            return back()->withErrors(['category' => 'Cannot delete a category that has articles or subcategories.']);
        }

        AuditLog::record('category.deleted', $category, ['name' => $category->name]);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    /**
     * Bulk reorder via drag-and-drop in the admin UI.
     * Expects: { order: [{id: 1, order: 0}, {id: 2, order: 1}, ...] }
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'exists:categories,id'],
            'order.*.order' => ['required', 'integer'],
        ]);

        foreach ($request->input('order') as $row) {
            Category::where('id', $row['id'])->update(['order' => $row['order']]);
        }

        return response()->json(['status' => 'ok']);
    }
}
