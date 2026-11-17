<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesSiteLanguage;
use App\Models\JobVacancy;
use Illuminate\Http\Request;

/**
 * Public web detail page for a job vacancy: GET /jobs/{id}
 * (the app share link /job/{id} is separate and handled by ShareController).
 */
class JobDetailController extends Controller
{
    use ResolvesSiteLanguage;

    private const SIMILAR = 6;

    public function show(Request $request, int $id)
    {
        // Same public rule as the listing: active, open or filled, not expired. Anything else is a 404.
        $job = JobVacancy::active()->findOrFail($id);

        $lang = $this->resolveSiteLanguage($request);

        // Other open jobs, same job type and/or same location first, then newest.
        $similar = JobVacancy::active()
            ->where('status', 'open')
            ->whereKeyNot($job->id)
            ->orderByRaw(
                '(CASE WHEN job_type = ? THEN 0 ELSE 1 END) + (CASE WHEN location = ? THEN 0 ELSE 1 END)',
                [$job->job_type, $job->location]
            )
            ->latest()
            ->orderByDesc('id')
            ->limit(self::SIMILAR)
            ->get(['id', 'title', 'company_name', 'job_type', 'location', 'salary_min', 'salary_max', 'salary_type', 'created_at']);

        $response = response()->view('job-detail', [
            'lang'      => $lang,
            'activeNav' => 'jobs',
            'job'       => $job,
            'similar'   => $similar,
        ]);

        return $this->rememberSiteLanguage($response, $request, $lang);
    }
}
