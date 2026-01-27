<?php

namespace App\Http\Controllers;

use App\Models\BusinessReview;
use App\Models\Business;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessReviewController extends Controller
{
    /**
     * Store a new review.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'user_id' => 'required|exists:app_users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        // Check if user is registered (has email)
        $user = AppUser::find($request->user_id);
        if (!$user->is_login || !$user->email) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Guest users cannot add reviews. Please register to continue.',
                'code' => 'GUEST_RESTRICTION'
            ], 403);
        }

        // Check if user already reviewed this business
        $existing = BusinessReview::where('business_id', $request->business_id)
            ->where('app_user_id', $request->user_id)
            ->first();

        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'You have already reviewed this business.'], 400);
        }

        $review = BusinessReview::create([
            'business_id' => $request->business_id,
            'app_user_id' => $request->user_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json(['status' => 'success', 'message' => 'Review added successfully', 'data' => $review]);
    }

    /**
     * Update a review.
     */
    public function update(Request $request, $id)
    {
        $review = BusinessReview::findOrFail($id);
        
        if ($review->app_user_id != $request->user_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json(['status' => 'success', 'message' => 'Review updated successfully', 'data' => $review]);
    }

    /**
     * Delete a review.
     */
    public function destroy(Request $request, $id)
    {
        $review = BusinessReview::findOrFail($id);
        
        if ($review->app_user_id != $request->user_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['status' => 'success', 'message' => 'Review deleted successfully']);
    }

    /**
     * Get reviews for a business.
     */
    public function getBusinessReviews($businessId)
    {
        $reviews = BusinessReview::where('business_id', $businessId)
            ->with(['user' => function($q) {
                $q->select('id', 'name');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ]);
    }
}
