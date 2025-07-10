<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsCategory;
use Illuminate\Support\Str;

class NewsCategoryController extends Controller
{
    /**
     * Display a listing of news categories.
     */
    public function index(Request $request)
    {
        $query = NewsCategory::query();

        // Search functionality
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Status filter
        if ($request->status !== null && $request->status !== '') {
            if ($request->status === 'active') {
                $query->where('status', true);
            } elseif ($request->status === 'inactive') {
                $query->where('status', false);
            }
        }

        // Sort by
        if ($request->sort_by) {
            switch ($request->sort_by) {
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->ordered();
            }
        } else {
            $query->ordered();
        }

        $newsCategories = $query->paginate(10);

        return view('admin.newsCategories.index', compact('newsCategories'));
    }

    /**
     * Store a newly created news category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:news_categories,name',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category name already exists.',
            'color.required' => 'Category color is required.',
            'color.regex' => 'Please enter a valid hex color code.',
        ]);

        try {
            $newsCategory = NewsCategory::create([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
                'status' => $request->has('status') ? true : false,
                'sort_order' => $request->sort_order ?? 0,
            ]);

            \Log::info('News category created successfully', [
                'category_id' => $newsCategory->id,
                'category_name' => $newsCategory->name,
                'created_by' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->route('admin.news-categories')
                           ->with('success', 'News category created successfully!');

        } catch (\Exception $e) {
            \Log::error('Error creating news category: ' . $e->getMessage());
            return back()->with('error', 'Error creating category: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Show the form for editing the specified news category.
     */
    public function edit($id)
    {
        try {
            $newsCategory = NewsCategory::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $newsCategory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }
    }

    /**
     * Update the specified news category.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:news_categories,id',
            'name' => 'required|string|max:255|unique:news_categories,name,' . $request->id,
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category name already exists.',
            'color.required' => 'Category color is required.',
            'color.regex' => 'Please enter a valid hex color code.',
        ]);

        try {
            $newsCategory = NewsCategory::findOrFail($request->id);
            
            $newsCategory->update([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
                'status' => $request->has('status') ? true : false,
                'sort_order' => $request->sort_order ?? 0,
            ]);

            \Log::info('News category updated successfully', [
                'category_id' => $newsCategory->id,
                'category_name' => $newsCategory->name,
                'updated_by' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->route('admin.news-categories')
                           ->with('success', 'News category updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Error updating news category: ' . $e->getMessage());
            return back()->with('error', 'Error updating category: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Remove the specified news category.
     */
    public function destroy($id)
    {
        try {
            $newsCategory = NewsCategory::findOrFail($id);
            
            // Future: Check if category has articles
            // if ($newsCategory->articles()->count() > 0) {
            //     return back()->with('error', 'Cannot delete category that has articles.');
            // }

            $categoryName = $newsCategory->name;
            $newsCategory->delete();

            \Log::info('News category deleted successfully', [
                'category_id' => $id,
                'category_name' => $categoryName,
                'deleted_by' => auth()->user()->id ?? 'system'
            ]);

            return redirect()->route('admin.news-categories')
                           ->with('success', 'News category deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Error deleting news category: ' . $e->getMessage());
            return back()->with('error', 'Error deleting category: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of a news category.
     */
    public function toggleStatus($id)
    {
        try {
            $newsCategory = NewsCategory::findOrFail($id);
            $newsCategory->status = !$newsCategory->status;
            $newsCategory->save();

            \Log::info('News category status toggled', [
                'category_id' => $id,
                'new_status' => $newsCategory->status,
                'updated_by' => auth()->user()->id ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'status' => $newsCategory->status ? 'active' : 'inactive',
                'message' => 'Status updated successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error toggling news category status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating status.'
            ], 500);
        }
    }

    /**
     * Get news categories for API (mobile app, etc.)
     */
    public function getNewsCategories()
    {
        try {
            $categories = NewsCategory::active()
                                    ->ordered()
                                    ->select('id', 'name', 'description', 'slug', 'color')
                                    ->get();

            return response()->json([
                'status' => true,
                'message' => 'News categories fetched successfully',
                'data' => $categories,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching news categories: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error fetching categories',
                'data' => [],
            ], 500);
        }
    }
}