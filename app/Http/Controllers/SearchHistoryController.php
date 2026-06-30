<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchHistoryController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    //  HELPER — validate user is logged-in (not guest)
    // ─────────────────────────────────────────────────────────────────────

    private function resolveLoggedInUser(Request $request): ?AppUser
    {
        $userId = $request->input('user_id');
        if (!$userId) return null;

        $user = AppUser::find($userId);
        if (!$user) return null;

        // Guest users have is_login = false
        if (!$user->is_login) return null;

        return $user;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  API: GET /api/search-history?user_id=xxx
    //  List the most recent searches for a user
    // ─────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        try {
            $user = $this->resolveLoggedInUser($request);

            if (!$user) {
                return response()->json([
                    'status'  => 'unauthorized',
                    'message' => 'Please login to view your search history.',
                ], 401);
            }

            $history = SearchHistory::where('app_user_id', $user->id)
                ->orderByDesc('updated_at')
                ->limit(20)
                ->get(['id', 'query']);

            return response()->json([
                'status' => 'success',
                'data'   => $history,
            ]);
        } catch (\Throwable $e) {
            Log::error('Search history index failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not load search history.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  API: POST /api/search-history  { user_id, query }
    //  Record a search term (de-duplicated, newest bubbles to top)
    // ─────────────────────────────────────────────────────────────────────

    public function record(Request $request)
    {
        try {
            $user = $this->resolveLoggedInUser($request);

            if (!$user) {
                return response()->json([
                    'status'  => 'unauthorized',
                    'message' => 'Please login to save your search history.',
                ], 401);
            }

            $request->validate([
                'query' => 'required|string|max:191',
            ]);

            $query = trim($request->input('query'));
            if ($query === '') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Search query cannot be empty.',
                ], 422);
            }

            // De-duplicate per user; bump updated_at so it moves to the top.
            $history = SearchHistory::updateOrCreate(
                ['app_user_id' => $user->id, 'query' => $query],
                []
            );
            $history->touch();

            return response()->json([
                'status' => 'success',
                'data'   => ['id' => $history->id, 'query' => $history->query],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Search history record failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not save search history.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  API: DELETE /api/search-history/{id}?user_id=xxx
    //  Delete a single history item (owned by the user)
    // ─────────────────────────────────────────────────────────────────────

    public function destroy(Request $request, $id)
    {
        try {
            $user = $this->resolveLoggedInUser($request);

            if (!$user) {
                return response()->json([
                    'status'  => 'unauthorized',
                    'message' => 'Please login to manage your search history.',
                ], 401);
            }

            $deleted = SearchHistory::where('id', $id)
                ->where('app_user_id', $user->id)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Search history item not found.',
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Search history item removed.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Search history destroy failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not remove search history item.',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  API: DELETE /api/search-history/clear?user_id=xxx
    //  Clear all history for the user
    // ─────────────────────────────────────────────────────────────────────

    public function clear(Request $request)
    {
        try {
            $user = $this->resolveLoggedInUser($request);

            if (!$user) {
                return response()->json([
                    'status'  => 'unauthorized',
                    'message' => 'Please login to manage your search history.',
                ], 401);
            }

            SearchHistory::where('app_user_id', $user->id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Search history cleared.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Search history clear failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not clear search history.',
            ], 500);
        }
    }
}
