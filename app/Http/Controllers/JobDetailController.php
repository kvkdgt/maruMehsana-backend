<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesSiteLanguage;
use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $this->countWebVisit($request, $job->id);

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

    /**
     * Count one website view in `web_visitors`, kept apart from the app's `views_count`
     * (which drives the poster's view-milestone notifications). Skips HEAD requests and bots.
     */
    private function countWebVisit(Request $request, int $id): void
    {
        static $hasColumn = null;

        if ($request->isMethod('HEAD')) {
            return;
        }
        $ua = (string) $request->userAgent();
        if ($ua === '' || preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|fetch|monitor|curl|wget|python|java\/|httpclient|headless|lighthouse/i', $ua)) {
            return;
        }
        try {
            $hasColumn ??= Schema::hasColumn('job_vacancies', 'web_visitors');
            if ($hasColumn) {
                // Query builder, not Eloquent: a view must not bump updated_at.
                DB::table('job_vacancies')->where('id', $id)->increment('web_visitors');
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
