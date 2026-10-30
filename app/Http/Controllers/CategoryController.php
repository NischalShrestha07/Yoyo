<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request, Business $business): View
    {
        $this->authorize('viewAny', [Category::class, $business]);

        $categories = Category::where('business_id', $business->id)
            ->with('parent')
            ->withCount(['products', 'services'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('business.categories.index', compact('business', 'categories'));
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Category::class, $business]);

        $parents = Category::where('business_id', $business->id)->where('is_active', true)->get();

        return view('business.categories.create', compact('business', 'parents'));
    }

    public function store(StoreCategoryRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('create', [Category::class, $business]);

        Category::create([
            'business_id' => $business->id,
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'description' => $request->description,
            'image_path' => $request->image_path,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('business.categories.index', $business)
            ->with('success', 'Category created successfully.');
    }

    public function show(Business $business, Category $category)
    {
        $this->authorize('view', [$category, $business]);

        $category->load(['parent', 'children', 'products', 'services']);

        if (view()->exists('business.categories.show')) {
            return view('business.categories.show', compact('business', 'category'));
        }

        return response()->json(['category' => $category]);
    }

    public function edit(Business $business, Category $category): View
    {
        $this->authorize('update', [$category, $business]);

        $parents = Category::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('id', '!=', $category->id)
            ->get();

        return view('business.categories.edit', compact('business', 'category', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Business $business, Category $category): RedirectResponse
    {
        $this->authorize('update', [$category, $business]);

        $this->assertParentIsNotDescendant($category, $request->parent_id);

        $category->update([
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'description' => $request->description,
            'image_path' => $request->image_path,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $category->is_active,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('business.categories.index', $business)
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Prevent moving a category underneath one of its own descendants, which
     * would detach the branch from the tree.
     */
    private function assertParentIsNotDescendant(Category $category, ?int $parentId): void
    {
        $current = $parentId
            ? Category::where('business_id', $category->business_id)->find($parentId)
            : null;

        $visited = [];

        while ($current) {
            if ((int) $current->id === (int) $category->id || in_array($current->id, $visited, true)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'A category cannot be nested inside itself or one of its descendants.',
                ]);
            }

            $visited[] = $current->id;
            $current = $current->parent_id
                ? Category::where('business_id', $category->business_id)->find($current->parent_id)
                : null;
        }
    }

    public function destroy(Business $business, Category $category): RedirectResponse
    {
        $this->authorize('delete', [$category, $business]);

        $category->update(['is_active' => false]);

        return redirect()->route('business.categories.index', $business)
            ->with('success', 'Category archived successfully.');
    }
}
