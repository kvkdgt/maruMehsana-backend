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
use Illuminate\Support\Facades\Log;

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

        $article = NewsArticle::create($data);

        // Handle notification scheduling
        if ($article->is_active) {
            try {
                $notificationResult = $this->notificationController->scheduleNewsNotification($article);
                
                if ($notificationResult['success']) {
                    $scheduledTime = $notificationResult['scheduled_at']->format('Y-m-d H:i:s');
                    $message = "News article created successfully! Notification scheduled for {$scheduledTime}";
                    
                    Log::info('News article created with scheduled notification', [
                        'article_id' => $article->id,
                        'notification_id' => $notificationResult['notification_id'],
                        'scheduled_at' => $scheduledTime
                    ]);
                } else {
                    $message = 'News article created successfully, but notification scheduling failed: ' . $notificationResult['message'];
                    
                    Log::warning('News article created but notification scheduling failed', [
                        'article_id' => $article->id,
                        'error' => $notificationResult['message']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Notification scheduling failed during article creation', [
                    'article_id' => $article->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $message = 'News article created successfully, but notification scheduling failed due to system error.';
            }
        } else {
            $message = 'News article created successfully! (No notification scheduled - article is inactive)';
            
            Log::info('News article created (inactive)', [
                'article_id' => $article->id,
                'status' => 'inactive'
            ]);
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

        // Handle notification scheduling for newly activated articles
        if ($wasInactive && $news->is_active) {
            try {
                $notificationResult = $this->notificationController->scheduleNewsNotification($news);
                
                if ($notificationResult['success']) {
                    $scheduledTime = $notificationResult['scheduled_at']->format('Y-m-d H:i:s');
                    $message = "News article updated and notification scheduled for {$scheduledTime}!";
                    
                    Log::info('News article activated with scheduled notification', [
                        'article_id' => $news->id,
                        'notification_id' => $notificationResult['notification_id'],
                        'scheduled_at' => $scheduledTime,
                        'action' => 'activation_update'
                    ]);
                } else {
                    $message = 'News article updated successfully, but notification scheduling failed: ' . $notificationResult['message'];
                    
                    Log::warning('News article activated but notification scheduling failed', [
                        'article_id' => $news->id,
                        'error' => $notificationResult['message'],
                        'action' => 'activation_update'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Notification scheduling failed during article update', [
                    'article_id' => $news->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'action' => 'activation_update'
                ]);
                $message = 'News article updated successfully, but notification scheduling failed due to system error.';
            }
        } else {
            $message = 'News article updated successfully!';
            
            Log::info('News article updated', [
                'article_id' => $news->id,
                'was_inactive' => $wasInactive,
                'is_active_now' => $news->is_active,
                'action' => 'regular_update'
            ]);
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

        // Cancel any pending notifications for this news article
        $pendingNotifications = \App\Models\Notification::where('news_article_id', $news->id)
            ->where('is_sent', false)
            ->get();

        foreach ($pendingNotifications as $notification) {
            $notification->delete();
            Log::info('Cancelled pending notification due to news deletion', [
                'notification_id' => $notification->id,
                'news_id' => $news->id
            ]);
        }

        // Delete image if exists
        if ($news->image) {
            Storage::delete('public/news/' . $news->image);
        }

        Log::info('News article deleted', [
            'article_id' => $news->id,
            'cancelled_notifications' => $pendingNotifications->count()
        ]);

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