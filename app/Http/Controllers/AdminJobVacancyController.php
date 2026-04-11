<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobVacancy;
use App\Models\JobReport;
use Illuminate\Support\Facades\Log;

class AdminJobVacancyController extends Controller
{
    /**
     * GET /api/admin/jobs/reported
     * Jobs with 3+ reports, with reason breakdown and reporter list.
     */
    public function reportedJobs(Request $request)
    {
        try {
            $jobs = JobVacancy::withCount('reports')
                ->having('reports_count', '>=', 3)
                ->with(['reports' => function ($q) {
                    $q->select('id', 'job_vacancy_id', 'reported_by', 'reason', 'description', 'status', 'created_at')
                      ->with('reporter:id,name,email');
                }, 'poster:id,name,email'])
                ->orderByDesc('reports_count')
                ->paginate($request->input('per_page', 20));

            return response()->json([
                'status' => 'success',
                'data'   => $jobs->items(),
                'pagination' => [
                    'current_page' => $jobs->currentPage(),
                    'last_page'    => $jobs->lastPage(),
                    'total'        => $jobs->total(),
                    'has_more'     => $jobs->hasMorePages(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('AdminJobVacancy reportedJobs error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 500);
        }
    }

    /**
     * DELETE /api/admin/jobs/{id}
     * Remove a spam/reported job by setting is_active = 0.
     */
    public function removeJob($id, Request $request)
    {
        try {
            $job = JobVacancy::find($id);

            if (!$job) {
                return response()->json(['status' => 'error', 'message' => 'Job not found.'], 404);
            }

            $job->is_active = 0;
            $job->save();

            // Mark all reports on this job as reviewed
            JobReport::where('job_vacancy_id', $id)->update(['status' => 'reviewed']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Job removed successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('AdminJobVacancy removeJob error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 500);
        }
    }

    /**
     * GET /api/admin/jobs
     * All jobs with filters (for admin panel listing).
     */
    public function allJobs(Request $request)
    {
        try {
            $query = JobVacancy::with(['poster:id,name'])->withCount('reports');

            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            if ($isActive = $request->input('is_active')) {
                $query->where('is_active', (int) $isActive);
            }

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%");
                });
            }

            $jobs = $query->latest()->paginate($request->input('per_page', 20));

            return response()->json([
                'status' => 'success',
                'data'   => $jobs->items(),
                'pagination' => [
                    'current_page' => $jobs->currentPage(),
                    'last_page'    => $jobs->lastPage(),
                    'total'        => $jobs->total(),
                    'has_more'     => $jobs->hasMorePages(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('AdminJobVacancy allJobs error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 500);
        }
    }
}
