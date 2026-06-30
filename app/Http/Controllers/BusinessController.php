<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Category;
use App\Models\BusinessImages;
use App\Models\AppUser;
use App\Models\Order;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    public function createView()
    {
        $categories = Category::get();
        $appUsers = AppUser::orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.business-form', compact('categories', 'appUsers'));
    }

    /**
     * Friendly, user-facing messages for thumbnail / image validation.
     */
    private function imageValidationMessages(): array
    {
        return [
            'thumbnail.required' => 'Please select a thumbnail image.',
            'thumbnail.image'    => 'The thumbnail must be an image file.',
            'thumbnail.mimes'    => 'The thumbnail must be a JPG, PNG, GIF or SVG file.',
            'thumbnail.max'      => 'The thumbnail is too large. Please use an image of :max KB (about 5 MB) or smaller.',
            'images.*.image'     => 'Each business image must be a valid image file.',
            'images.*.mimes'     => 'Each business image must be a JPG, PNG, GIF or SVG file.',
            'images.*.max'       => 'One of the business images is too large. Each image must be :max KB (about 5 MB) or smaller — please remove or replace it.',
        ];
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg', // Optional additional images
            'category_id' => 'required|integer|exists:categories,id',
            'owner_id' => 'nullable|integer|exists:app_users,id',
            'mobile' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'website' => 'nullable|url|max:255', // Optional website link
            'email' => 'nullable|email|max:255',
            'products' => 'nullable|string',
        ], $this->imageValidationMessages());
        $thumbnailPath = $request->file('thumbnail')->store('business_thumbnails', 'public');
        $business = Business::create([
            'name' => $request->name,
            'description' => $request->description,
            'thumbnail' => $thumbnailPath,
            'category_id' => $request->category_id,
            'owner_id' => $request->owner_id ?: null,
            'visitors' => 0, // Initially set to 0
            'mobile_no' => $request->mobile, // Optional
            'whatsapp_no' => $request->whatsapp, // Optional
            'website_url' => $request->website, // Optional
            'email_id' => $request->email,
            'services' => $request->services,  
            'products' => $request->products, 
            'created_by' => Auth::id(),
        ]);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('business_images', 'public');

                BusinessImages::create([
                    'image' => $imagePath,
                    'business_id' => $business->id,
                ]);
            }
        }
        return redirect()->route('admin.businesses')->with('success', 'Business created successfully!');
    }
    public function getBusinessById($id)
    {
        $business = Business::with(['businessImages', 'owner'])->findOrFail($id);
        $categories = Category::get();
        $appUsers = AppUser::orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.business-form', compact('categories', 'business', 'appUsers'));

        // return redirect()->route('admin.businesses')->with('success', 'Business deleted successfully!');
    }

    // Admin detail page for a single business
    public function show($id)
    {
        $business = Business::with([
            'category',
            'businessImages',
            'creator',
            'updater',
            'owner',
            'products.options',
            'reviews' => function ($query) {
                $query->with('user:id,name')->orderBy('created_at', 'desc');
            },
        ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        return view('admin.business-detail', compact('business'));
    }

    // API: businesses owned by a given app user (for the app's "My Businesses")
    public function ownedBusinesses(Request $request)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'user_id is required'], 422);
        }

        $businesses = Business::with('category:id,name')
            ->where('owner_id', $userId)
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'thumbnail', 'category_id', 'visitors'])
            ->map(function ($b) {
                return [
                    'id'        => $b->id,
                    'name'      => $b->name,
                    'thumbnail' => $b->thumbnail,
                    'category'  => $b->category?->name,
                    'visitors'  => $b->visitors,
                ];
            });

        return response()->json(['status' => 'success', 'data' => $businesses]);
    }

    // API: owner requests home-delivery access (requires owner to have a mobile number)
    public function requestDelivery(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|integer',
            'business_id' => 'required|integer|exists:businesses,id',
        ]);

        $business = Business::find($request->business_id);
        if (!$business || (int) $business->owner_id !== (int) $request->user_id) {
            return response()->json(['status' => 'error', 'message' => 'You are not the owner of this business.'], 403);
        }

        $user = AppUser::find($request->user_id);
        if (!$user || empty($user->phone)) {
            return response()->json([
                'status'  => 'need_mobile',
                'message' => 'Please add your mobile number in Edit Profile before requesting home delivery.',
            ], 422);
        }

        if ($business->delivery_status === 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Home delivery is already enabled for this business.'], 422);
        }
        if ($business->delivery_status === 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Your delivery request is already pending review.'], 422);
        }

        $business->delivery_status = 'pending';
        $business->delivery_requested_at = now();
        $business->delivery_reject_reason = null;
        $business->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Delivery request submitted. Our team will review it shortly.',
            'data'    => $business,
        ]);
    }

    // ADMIN (web): list delivery requests
    public function adminDeliveryRequests(Request $request)
    {
        $status = $request->get('status');
        $query = Business::with('owner:id,name,email,phone')->whereNotNull('delivery_status');
        if ($status) {
            $query->where('delivery_status', $status);
        }
        $requests = $query->orderByDesc('delivery_requested_at')->paginate(15);
        return view('admin.delivery-requests', compact('requests'));
    }

    // ADMIN (web): approve / reject a delivery request
    public function adminUpdateDeliveryStatus(Request $request, $id)
    {
        $request->validate([
            'status'        => 'required|in:approved,rejected',
            'reject_reason' => 'nullable|string|max:255',
        ]);

        $business = Business::findOrFail($id);
        $business->delivery_status = $request->status;
        $business->delivery_reject_reason = $request->status === 'rejected' ? $request->reject_reason : null;
        $business->save();

        if ($business->owner_id) {
            if ($request->status === 'approved') {
                PushNotificationService::sendToUser(
                    $business->owner_id,
                    'Delivery Approved ✅',
                    "\"{$business->name}\" can now accept orders. Start adding your products!",
                    ['type' => 'delivery', 'business_id' => $business->id]
                );
            } else {
                PushNotificationService::sendToUser(
                    $business->owner_id,
                    'Delivery Request Update',
                    $request->reject_reason ?: "Your delivery request for \"{$business->name}\" was not approved.",
                    ['type' => 'delivery', 'business_id' => $business->id]
                );
            }
        }

        return redirect()->back()->with('success', 'Delivery request ' . $request->status . '.');
    }

    // API: owner's view of a single business they own (no visitor increment, ownership enforced)
    public function ownedBusinessShow(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|integer',
            'business_id' => 'required|integer|exists:businesses,id',
        ]);

        $business = Business::with([
                'category:id,name',
                'businessImages',
                'reviews' => function ($q) {
                    $q->with('user:id,name,profile_picture')->orderByDesc('created_at');
                },
            ])
            ->withCount('reviews')
            ->find($request->business_id);

        if (!$business) {
            return response()->json(['status' => 'error', 'message' => 'Business not found'], 404);
        }
        if ((int) $business->owner_id !== (int) $request->user_id) {
            return response()->json(['status' => 'error', 'message' => 'You are not the owner of this business.'], 403);
        }

        // Analytics
        $business->avg_rating = round($business->reviews->avg('rating') ?? 0, 1);

        // Order analytics
        $business->orders_total     = Order::where('business_id', $business->id)->count();
        $business->orders_delivered = Order::where('business_id', $business->id)->where('status', 'delivered')->count();
        $business->orders_active    = Order::where('business_id', $business->id)->whereIn('status', ['requested', 'confirmed', 'dispatched'])->count();
        $business->orders_revenue   = Order::where('business_id', $business->id)->where('status', 'delivered')->sum('total_amount');

        return response()->json(['status' => 'success', 'data' => $business]);
    }

    // API: owner updates the editable fields of a business they own
    public function updateOwnedBusiness(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|integer',
            'business_id' => 'required|integer|exists:businesses,id',
            'mobile'      => 'nullable|string|max:20',
            'whatsapp'    => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'website'     => 'nullable|url|max:255',
            'products'    => 'nullable|string',
            'services'    => 'nullable|string',
        ]);

        $business = Business::find($request->business_id);
        if (!$business) {
            return response()->json(['status' => 'error', 'message' => 'Business not found'], 404);
        }
        if ((int) $business->owner_id !== (int) $request->user_id) {
            return response()->json(['status' => 'error', 'message' => 'You are not allowed to edit this business.'], 403);
        }

        $business->update([
            'mobile_no'   => $request->mobile,
            'whatsapp_no' => $request->whatsapp,
            'email_id'    => $request->email,
            'website_url' => $request->website,
            'products'    => $request->products,
            'services'    => $request->services,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Business updated successfully',
            'data'    => $business->fresh(['category:id,name', 'businessImages']),
        ]);
    }
    public function destroy($id)
    {
        $business = Business::findOrFail($id);

        // Check if the thumbnail exists and delete it
        if ($business->thumbnail) {
            $thumbnailPath = 'public/' . $business->thumbnail; // Path relative to storage

            if (Storage::exists($thumbnailPath)) {
                Storage::delete($thumbnailPath); // Delete the file from storage
            } else {
                // Log if the file doesn't exist
                dd("Thumbnail file not found: " . $thumbnailPath);
            }
        }

        // Delete the business record
        $business->delete();

        return redirect()->route('admin.businesses')->with('success', 'Business deleted successfully!');
    }

    public function deleteImage($id)
    {
        $businessImage = BusinessImages::findOrFail($id);

        if ($businessImage->deleteImage()) {
            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to delete the image.']);
    }

    public function update(Request $request)
    {
        $business = Business::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg', // Optional thumbnail
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg', // Optional additional images
            'category_id' => 'required|integer|exists:categories,id',
            'owner_id' => 'nullable|integer|exists:app_users,id',
            'mobile' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'website' => 'nullable|url|max:255', // Optional website link
            'email' => 'nullable|email|max:255',
            'services' => 'nullable|string',
            'products' => 'nullable|string',
    ], $this->imageValidationMessages());

        // Update basic fields
        $business->update([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'owner_id' => $request->owner_id ?: null,
            'mobile_no' => $request->mobile, // Optional
            'whatsapp_no' => $request->whatsapp, // Optional
            'website_url' => $request->website, // Optional
            'email_id' => $request->email, // Optional
            'services' => $request->services,
            'products' => $request->products, 
            'updated_by' => Auth::id(),
        ]);

        // Update thumbnail if provided
        if ($request->hasFile('thumbnail')) {
            // Delete the old thumbnail if it exists
            if ($business->thumbnail && Storage::exists('public/' . $business->thumbnail)) {
                Storage::delete('public/' . $business->thumbnail);
            }

            // Store the new thumbnail
            $thumbnailPath = $request->file('thumbnail')->store('business_thumbnails', 'public');
            $business->update(['thumbnail' => $thumbnailPath]);
        }

        // Add new images if provided
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('business_images', 'public');

                BusinessImages::create([
                    'image' => $imagePath,
                    'business_id' => $business->id,
                ]);
            }
        }

        return redirect()->route('admin.businesses')->with('success', 'Business updated successfully!');
    }
    // api
    public function getBusinesses(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'limit' => 'nullable|integer|min:1',
            'page' => 'nullable|integer|min:1',
            'isFromTrendingCategory' => 'nullable|string|in:true,false',
        ]);

        $limit = $request->limit ?? 10; // Default limit per page
        $page = $request->page ?? 1; // Default page number is 1
        $offset = ($page - 1) * $limit; // Calculate offset for pagination
        $isFromTrendingCategory = $request->isFromTrendingCategory == 'true';

        if (!$isFromTrendingCategory) {
            Category::where('id', $request->category_id)->increment('category_visitors');
        }
        
    
        // Fetch businesses with limit and offset
        $businesses = Business::where('category_id', $request->category_id)
            ->offset($offset)
            ->limit($limit)
            ->get();
            
        // Add average rating to each business
        foreach ($businesses as $business) {
            $avgRating = $business->reviews()->avg('rating');
            $business->avg_rating = $avgRating ? round($avgRating, 1) : 0;
        }

        // Get total businesses count for the given category
        $totalBusinesses = Business::where('category_id', $request->category_id)->count();

        return response()->json([
            'status' => true,
            'message' => 'Businesses fetched successfully',
            'data' => $businesses,
            'pagination' => [
                'current_page' => $page,
                'limit' => $limit,
                'total_records' => $totalBusinesses,
                'total_pages' => ceil($totalBusinesses / $limit),
            ],
        ]);
    }

    public function getBusinessByIdAPI(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
        ]);
        
        Business::where('id', $request->business_id)->increment('visitors');
        
        // Fetch business details along with category, images, and basic review stats
        $business = Business::with(['category:id,name', 'businessImages'])
            ->withCount('reviews')
            ->where('id', $request->business_id)
            ->first();

        if (!$business) {
            return response()->json([
                'status' => false,
                'message' => 'Business not found',
            ], 404);
        }

        // Calculate average rating manually to be precise
        $avgRating = $business->reviews()->avg('rating');
        $business->avg_rating = $avgRating ? round($avgRating, 1) : 0;
        
        // Get recent 5 reviews
        $recentReviews = $business->reviews()
            ->with(['user:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $business->recent_reviews = $recentReviews;
    
        return response()->json([
            'status' => true,
            'message' => 'Business details fetched successfully',
            'data' => $business,
        ]);
    }
    
}
