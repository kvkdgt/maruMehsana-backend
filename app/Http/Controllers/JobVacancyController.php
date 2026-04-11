<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobVacancy;
use App\Models\JobReport;
use App\Models\JobSave;
use App\Models\AppUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class JobVacancyController extends Controller
{
    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function getAuthUser(Request $request, bool $requireLogin = false)
    {
        $userId = $request->input('user_id');
        if (!$userId) return null;
        $user = AppUser::find($userId);
        if ($requireLogin && (!$user || !$user->is_login)) return null;
        return $user;
    }

    private function jobTypeLabel(string $type): string
    {
        return match($type) {
            'full_time'   => 'Full Time',
            'part_time'   => 'Part Time',
            'freelance'   => 'Freelance',
            'internship'  => 'Internship',
            'contract'    => 'Contract',
            default       => $type,
        };
    }

    // ─── Public Endpoints ─────────────────────────────────────────────────────

    /**
     * GET /api/jobs
     * Public job listing (paginated, 15/page). Adds is_saved flag if user_id provided.
     */
    public function index(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $query = JobVacancy::active();

            // Search
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            }

            // Filter by job_type
            if ($jobType = $request->input('job_type')) {
                $query->where('job_type', $jobType);
            }

            // Filter by location
            if ($location = $request->input('location')) {
                $query->where('location', 'like', "%{$location}%");
            }

            // Filter by salary_min
            if ($salaryMin = $request->input('salary_min')) {
                $query->where('salary_max', '>=', $salaryMin);
            }

            // Sort
            $sort = $request->input('sort', 'latest');
            if ($sort === 'views') {
                $query->orderByDesc('views_count');
            } else {
                $query->latest();
            }

            $perPage = $request->input('per_page', 15);
            $jobs = $query->with(['poster:id,name'])->withCount('reports')->paginate($perPage);

            // Get saved job IDs for this user
            $savedIds = [];
            if ($userId) {
                $savedIds = JobSave::where('app_user_id', $userId)
                    ->pluck('job_vacancy_id')
                    ->toArray();
            }

            $data = $jobs->getCollection()->map(function ($job) use ($savedIds) {
                return $this->formatJobCard($job, $savedIds);
            });

            return response()->json([
                'status' => 'success',
                'data'   => $data,
                'pagination' => [
                    'current_page' => $jobs->currentPage(),
                    'last_page'    => $jobs->lastPage(),
                    'per_page'     => $jobs->perPage(),
                    'total'        => $jobs->total(),
                    'has_more'     => $jobs->hasMorePages(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('JobVacancy index error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 500);
        }
    }

    /**
     * GET /api/jobs/{id}
     * Job detail — increments views_count.
     */
    public function show($id, Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $job = JobVacancy::with(['poster:id,name'])->withCount('reports')->find($id);

            if (!$job || !$job->is_active) {
                return response()->json(['status' => 'error', 'message' => 'Job not found.'], 404);
            }

            // Increment views
            $job->increment('views_count');

            $savedIds = [];
            if ($userId) {
                $savedIds = JobSave::where('app_user_id', $userId)
                    ->pluck('job_vacancy_id')
                    ->toArray();
            }

            $hasReported = false;
            if ($userId) {
                $hasReported = JobReport::where('job_vacancy_id', $id)
                    ->where('reported_by', $userId)
                    ->exists();
            }

            $data = $this->formatJobDetail($job, $savedIds, $userId, $hasReported);

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('JobVacancy show error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 500);
        }
    }

    // ─── Authenticated Endpoints ──────────────────────────────────────────────

    /**
     * POST /api/jobs
     * Create a new job vacancy (logged-in users only).
     */
    public function store(Request $request)
    {
        $user = $this->getAuthUser($request, true);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Login required to post a job.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'title'           => 'required|string|max:255',
            'company_name'    => 'required|string|max:255',
            'job_type'        => 'required|in:full_time,part_time,freelance,internship,contract',
            'location'        => 'required|string|max:255',
            'salary_min'      => 'nullable|integer|min:0',
            'salary_max'      => 'nullable|integer|min:0',
            'salary_type'     => 'required|in:monthly,yearly,hourly,not_disclosed',
            'description'     => 'required|string',
            'requirements'    => 'nullable|string',
            'vacancies_count' => 'required|integer|min:1',
            'experience_required' => 'nullable|string|max:100',
            'education_required'  => 'nullable|string|max:255',
            'gender_preference'   => 'nullable|in:any,male,female',
            'contact_name'    => 'required|string|max:255',
            'contact_mobile'  => 'required|string|max:20',
            'contact_email'   => 'nullable|email|max:255',
            'apply_via'       => 'required|in:whatsapp,call,email,walk_in',
            'thumbnail'       => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'expires_at'      => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('job_thumbnails', 'public');
        }

        $job = JobVacancy::create([
            'posted_by'           => $user->id,
            'title'               => $request->title,
            'company_name'        => $request->company_name,
            'job_type'            => $request->job_type,
            'location'            => $request->location,
            'salary_min'          => $request->salary_min,
            'salary_max'          => $request->salary_max,
            'salary_type'         => $request->salary_type,
            'description'         => $request->description,
            'requirements'        => $request->requirements,
            'vacancies_count'     => $request->vacancies_count,
            'experience_required' => $request->experience_required,
            'education_required'  => $request->education_required,
            'gender_preference'   => $request->input('gender_preference', 'any'),
            'contact_name'        => $request->contact_name,
            'contact_mobile'      => $request->contact_mobile,
            'contact_email'       => $request->contact_email,
            'apply_via'           => $request->apply_via,
            'thumbnail'           => $thumbnailPath,
            'expires_at'          => $request->expires_at,
            'status'              => 'open',
            'is_active'           => 1,
            'views_count'         => 0,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Job posted successfully.',
            'data'    => $job,
        ], 201);
    }

    /**
     * PUT /api/jobs/{id}
     * Update own job post.
     */
    public function update($id, Request $request)
    {
        $user = $this->getAuthUser($request, true);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Login required.'], 401);
        }

        $job = JobVacancy::find($id);
        if (!$job || $job->posted_by != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Job not found or unauthorized.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'           => 'sometimes|required|string|max:255',
            'company_name'    => 'sometimes|required|string|max:255',
            'job_type'        => 'sometimes|required|in:full_time,part_time,freelance,internship,contract',
            'location'        => 'sometimes|required|string|max:255',
            'salary_min'      => 'nullable|integer|min:0',
            'salary_max'      => 'nullable|integer|min:0',
            'salary_type'     => 'sometimes|required|in:monthly,yearly,hourly,not_disclosed',
            'description'     => 'sometimes|required|string',
            'requirements'    => 'nullable|string',
            'vacancies_count' => 'sometimes|required|integer|min:1',
            'experience_required' => 'nullable|string|max:100',
            'education_required'  => 'nullable|string|max:255',
            'gender_preference'   => 'nullable|in:any,male,female',
            'contact_name'    => 'sometimes|required|string|max:255',
            'contact_mobile'  => 'sometimes|required|string|max:20',
            'contact_email'   => 'nullable|email|max:255',
            'apply_via'       => 'sometimes|required|in:whatsapp,call,email,walk_in',
            'thumbnail'       => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'expires_at'      => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($job->thumbnail && Storage::disk('public')->exists($job->thumbnail)) {
                Storage::disk('public')->delete($job->thumbnail);
            }
            $job->thumbnail = $request->file('thumbnail')->store('job_thumbnails', 'public');
        }

        $job->fill($request->except(['user_id', 'thumbnail', 'status', 'is_active', 'views_count', 'posted_by']));
        $job->save();

        return response()->json(['status' => 'success', 'message' => 'Job updated successfully.', 'data' => $job]);
    }

    /**
     * DELETE /api/jobs/{id}
     * Delete own job post.
     */
    public function destroy($id, Request $request)
    {
        $user = $this->getAuthUser($request, true);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Login required.'], 401);
        }

        $job = JobVacancy::find($id);
        if (!$job || $job->posted_by != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Job not found or unauthorized.'], 403);
        }

        if ($job->thumbnail && Storage::disk('public')->exists($job->thumbnail)) {
            Storage::disk('public')->delete($job->thumbnail);
        }

        $job->delete();

        return response()->json(['status' => 'success', 'message' => 'Job deleted successfully.']);
    }

    /**
     * PATCH /api/jobs/{id}/status
     * Toggle status: open / filled / closed. Own posts only.
     */
    public function updateStatus($id, Request $request)
    {
        $user = $this->getAuthUser($request, true);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Login required.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,filled,closed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $job = JobVacancy::find($id);
        if (!$job || $job->posted_by != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Job not found or unauthorized.'], 403);
        }

        $job->status = $request->status;
        $job->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Job status updated to ' . $request->status . '.',
            'data'    => ['id' => $job->id, 'status' => $job->status],
        ]);
    }

    /**
     * GET /api/jobs/my-posts
     * All jobs posted by the current user.
     */
    public function myPosts(Request $request)
    {
        $user = $this->getAuthUser($request, true);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Login required.'], 401);
        }

        $jobs = JobVacancy::where('posted_by', $user->id)
            ->withCount('reports')
            ->latest()
            ->paginate($request->input('per_page', 15));

        $data = $jobs->getCollection()->map(function ($job) {
            return $this->formatJobCard($job, []);
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'last_page'    => $jobs->lastPage(),
                'total'        => $jobs->total(),
                'has_more'     => $jobs->hasMorePages(),
            ],
        ]);
    }

    /**
     * POST /api/jobs/{id}/save
     * Toggle save (bookmark) for a job.
     */
    public function toggleSave($id, Request $request)
    {
        $user = $this->getAuthUser($request, true);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Login required to save jobs.'], 401);
        }

        $job = JobVacancy::find($id);
        if (!$job || !$job->is_active) {
            return response()->json(['status' => 'error', 'message' => 'Job not found.'], 404);
        }

        $existing = JobSave::where('job_vacancy_id', $id)->where('app_user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'success', 'saved' => false, 'message' => 'Job removed from saved.']);
        } else {
            JobSave::create(['job_vacancy_id' => $id, 'app_user_id' => $user->id]);
            return response()->json(['status' => 'success', 'saved' => true, 'message' => 'Job saved.']);
        }
    }

    /**
     * GET /api/jobs/saved
     * Saved jobs for the current user.
     */
    public function savedJobs(Request $request)
    {
        $user = $this->getAuthUser($request, true);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Login required.'], 401);
        }

        $saves = JobSave::where('app_user_id', $user->id)
            ->with(['job' => function ($q) {
                $q->where('is_active', 1)->with('poster:id,name');
            }])
            ->latest()
            ->paginate($request->input('per_page', 15));

        $data = $saves->getCollection()
            ->filter(fn($s) => $s->job !== null)
            ->map(fn($s) => $this->formatJobCard($s->job, [$s->job_vacancy_id]));

        return response()->json([
            'status' => 'success',
            'data'   => array_values($data->toArray()),
            'pagination' => [
                'current_page' => $saves->currentPage(),
                'last_page'    => $saves->lastPage(),
                'total'        => $saves->total(),
                'has_more'     => $saves->hasMorePages(),
            ],
        ]);
    }

    /**
     * POST /api/jobs/{id}/report
     * Report a job as spam.
     */
    public function report($id, Request $request)
    {
        $user = $this->getAuthUser($request, true);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Login required to report.'], 401);
        }

        $job = JobVacancy::find($id);
        if (!$job || !$job->is_active) {
            return response()->json(['status' => 'error', 'message' => 'Job not found.'], 404);
        }

        // Prevent reporting own job
        if ($job->posted_by == $user->id) {
            return response()->json(['status' => 'error', 'message' => 'You cannot report your own job.'], 422);
        }

        // Check duplicate report
        $alreadyReported = JobReport::where('job_vacancy_id', $id)
            ->where('reported_by', $user->id)
            ->exists();

        if ($alreadyReported) {
            return response()->json(['status' => 'error', 'message' => 'You have already reported this job.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason'      => 'required|in:spam,fake_job,inappropriate_content,other',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        JobReport::create([
            'job_vacancy_id' => $id,
            'reported_by'    => $user->id,
            'reason'         => $request->reason,
            'description'    => $request->description,
            'status'         => 'pending',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Report submitted. Thank you.']);
    }

    // ─── Private Formatters ───────────────────────────────────────────────────

    private function formatJobCard(JobVacancy $job, array $savedIds): array
    {
        $salary = 'Not Disclosed';
        if ($job->salary_type !== 'not_disclosed' && ($job->salary_min || $job->salary_max)) {
            $min = $job->salary_min ? '₹' . number_format($job->salary_min) : '';
            $max = $job->salary_max ? '₹' . number_format($job->salary_max) : '';
            $salary = $min && $max ? "$min - $max" : ($min ?: $max);
            $salary .= ' / ' . str_replace(['_'], [' '], $job->salary_type);
        }

        return [
            'id'             => $job->id,
            'title'          => $job->title,
            'company_name'   => $job->company_name,
            'location'       => $job->location,
            'job_type'       => $job->job_type,
            'job_type_label' => $this->jobTypeLabel($job->job_type),
            'salary_display' => $salary,
            'status'         => $job->status,
            'is_active'      => $job->is_active,
            'vacancies_count'=> $job->vacancies_count,
            'thumbnail'      => $job->thumbnail ? url('storage/' . $job->thumbnail) : null,
            'posted_by_name' => optional($job->poster)->name,
            'apply_via'      => $job->apply_via,
            'views_count'    => $job->views_count,
            'is_saved'       => in_array($job->id, $savedIds),
            'expires_at'     => $job->expires_at?->toDateString(),
            'created_at'     => $job->created_at,
        ];
    }

    private function formatJobDetail(JobVacancy $job, array $savedIds, $userId, bool $hasReported): array
    {
        $card = $this->formatJobCard($job, $savedIds);
        return array_merge($card, [
            'description'         => $job->description,
            'requirements'        => $job->requirements,
            'experience_required' => $job->experience_required,
            'education_required'  => $job->education_required,
            'gender_preference'   => $job->gender_preference,
            'contact_name'        => $job->contact_name,
            'contact_mobile'      => $job->contact_mobile,
            'contact_email'       => $job->contact_email,
            'salary_type'         => $job->salary_type,
            'salary_min'          => $job->salary_min,
            'salary_max'          => $job->salary_max,
            'is_own_post'         => $userId && $job->posted_by == $userId,
            'has_reported'        => $hasReported,
            'reports_count'       => $job->reports_count ?? 0,
        ]);
    }
}
