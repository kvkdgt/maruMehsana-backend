<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{
    // Show the form for creating a new category
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function create()
    {
        return view('admin.categories.create'); // Replace with your create view
    }

    // Store a newly created category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle image upload
        $imagePath = $request->file('image')->store('category_images', 'public');

        // Create the category
        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            'category_visitors' => 0,  // Initially set to 0
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.categories')->with('success', 'Category created successfully!');
    }

    // Show the form for editing a category
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category')); // Replace with your edit view
    }

    // Update the specified category
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $category = Category::find($request->id);

        // Handle image upload if exists
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('category_images', 'public');
            $category->image = $imagePath;
        }

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'updated_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Category updated successfully');
    }

    // Delete the specified category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.categories')->with('success', 'Category deleted successfully!');
    }

    // Show all categories
    public function index()
    {
        $categories = Category::all();
        return view('admin.categories', compact('categories')); // Replace with your index view
    }


    //apis for mobile 

    public function trendingCategories()
    {
        // Fetch top 5 categories ordered by category_visitors in descending order
        $categories = Category::orderByDesc('category_visitors')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Top 5 trending categories',
            'data' => $categories
        ]);
    }

    public function categories()
    {
        $categories = Category::all();
        return response()->json([
            'status' => true,
            'message' => 'All Categories',
            'data' => $categories
        ]);
    }
}
