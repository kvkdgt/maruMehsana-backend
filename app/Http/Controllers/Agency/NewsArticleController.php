<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsArticleRequest;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsArticle::where('agency_id', Auth::guard('agency')->id())
            ->latest();

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $articles = $query->paginate(10);

        return view('agency.news.index', compact('articles'));
    }

    public function create()
    {
        return view('agency.news.create');
    }

    public function store(NewsArticleRequest $request)
    {
        $data = $request->validated();
        $data['agency_id'] = Auth::guard('agency')->id();
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['is_for_mehsana'] = $request->has('is_for_mehsana');


        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($request->title) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/news', $imageName);
            $data['image'] = $imageName;
        }

        NewsArticle::create($data);

        return redirect()->route('agency.news.index')
            ->with('success', 'News article created successfully!');
    }

    public function show(NewsArticle $news)
    {
        // Ensure the article belongs to the current agency
        if ($news->agency_id !== Auth::guard('agency')->id()) {
            abort(403);
        }

        return view('agency.news.show', compact('news'));
    }

    public function edit(NewsArticle $news)
    {
        // Ensure the article belongs to the current agency
        if ($news->agency_id !== Auth::guard('agency')->id()) {
            abort(403);
        }

        return view('agency.news.edit', compact('news'));
    }

    public function update(NewsArticleRequest $request, NewsArticle $news)
    {
        // Ensure the article belongs to the current agency
        if ($news->agency_id !== Auth::guard('agency')->id()) {
            abort(403);
        }

        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['is_for_mehsana'] = $request->has('is_for_mehsana');


        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($news->image) {
                Storage::delete('public/news/' . $news->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($request->title) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/news', $imageName);
            $data['image'] = $imageName;
        }

        $news->update($data);

        return redirect()->route('agency.news.index')
            ->with('success', 'News article updated successfully!');
    }

    public function destroy(NewsArticle $news)
    {
        // Ensure the article belongs to the current agency
        if ($news->agency_id !== Auth::guard('agency')->id()) {
            abort(403);
        }

        // Delete image if exists
        if ($news->image) {
            Storage::delete('public/news/' . $news->image);
        }

        $news->delete();

        return redirect()->route('agency.news.index')
            ->with('success', 'News article deleted successfully!');
    }

    public function uploadImage(Request $request)
{
    $request->validate([
        'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    if ($request->hasFile('file')) {
        $image = $request->file('file');
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->storeAs('public/news/content', $imageName);
        
        return response()->json([
            'location' => asset('storage/news/content/' . $imageName)
        ]);
    }

    return response()->json(['error' => 'No file uploaded'], 400);
}
}