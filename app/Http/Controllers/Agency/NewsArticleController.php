<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsArticleRequest;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Admin\NotificationController;

class NewsArticleController extends Controller
{
     protected $notificationController;
     public function __construct()
    {
        // Initialize notification controller
        $this->notificationController = app(NotificationController::class);
    }
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
$article = NewsArticle::create($data);
         if ($article->is_active) {
            try {
                $notificationResult = $this->notificationController->sendNewsNotification($article);
                
                if ($notificationResult['success']) {
                    $message = 'News article created and notification sent successfully!';
                } else {
                    $message = 'News article created successfully, but notification failed to send.';
                }
            } catch (\Exception $e) {
                \Log::error('Notification sending failed: ' . $e->getMessage());
                $message = 'News article created successfully, but notification failed to send.';
            }
        } else {
            $message = 'News article created successfully! (No notification sent - article is inactive)';
        }

        return redirect()->route('agency.news.index')
            ->with('success', $message);
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
        $wasInactive = !$news->is_active; // Check if article was previously inactive
        
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $data['is_for_mehsana'] = $request->has('is_for_mehsana') ? 1 : 0;

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

        // Send notification if article was just activated (published for first time)
        if ($wasInactive && $news->is_active) {
            try {
                $notificationResult = $this->notificationController->sendNewsNotification($news);
                
                if ($notificationResult['success']) {
                    $message = 'News article updated and notification sent successfully!';
                } else {
                    $message = 'News article updated successfully, but notification failed to send.';
                }
            } catch (\Exception $e) {
                \Log::error('Notification sending failed: ' . $e->getMessage());
                $message = 'News article updated successfully, but notification failed to send.';
            }
        } else {
            $message = 'News article updated successfully!';
        }

        return redirect()->route('agency.news.index')
            ->with('success', $message);
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