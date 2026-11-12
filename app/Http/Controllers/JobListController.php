<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesSiteLanguage;
use App\Models\JobVacancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Public "Jobs" page: search, job type chips, sort, paginate (with infinite scroll).
 * GET /jobs?q=&type=full_time|part_time|freelance|internship|contract&sort=newest|salary|closing&page=
 * The job page (/jobs/{id}) lives in JobDetailController; /job/{jobId} is the app share deep link.
 *
 * Visibility is JobVacancy::active() (active, open or filled, not expired), the same rule as the app API
 * and the sitemap. Filled jobs stay listed with a "Filled" badge but always come after the open ones.
 */
class JobListController extends Controller
{
    use ResolvesSiteLanguage;

    public const TYPES = ['full_time', 'part_time', 'freelance', 'internship', 'contract'];
    private const SORTS = ['newest', 'salary', 'closing'];
    private const PER_PAGE = 12;

    public function index(Request $request)
    {
        $lang = $this->resolveSiteLanguage($request);

        // Same page rules as /news: ?page must be a whole number >= 2, otherwise the clean URL.
        // Infinite scroll (site.js) asks for the same URL with JSON: never redirect it, answer with cards instead.
        $wantsCards = $request->ajax() || $request->wantsJson();

        $rawPage = $request->query('page');
        $pageOk = is_string($rawPage) && ctype_digit($rawPage) && (int) $rawPage >= 1;
        if ($wantsCards && $rawPage !== null && !$pageOk) {
            return $this->cardsJson('', null);
        }
        if (!$wantsCards && $rawPage !== null && !($pageOk && (int) $rawPage > 1)) {
            return redirect()->to($request->fullUrlWithoutQuery('page'), 301);
        }
        $page = $rawPage === null ? 1 : min((int) $rawPage, 100000);

        $q = $request->query('q', '');
        $q = is_string($q) ? trim($q) : '';
        $q = mb_substr($q, 0, 80);
        $type = in_array($request->query('type'), self::TYPES, true) ? $request->query('type') : 'all';
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'newest';

        $base = JobVacancy::active();

        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $base->where(function (Builder $w) use ($like) {
                $w->where('title', 'like', $like)
                    ->orWhere('company_name', 'like', $like)
                    ->orWhere('location', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }
        // Chip counts follow the search text, so each chip says how many results it would show.
        $byType = (clone $base)->selectRaw('job_type, COUNT(*) as c')->groupBy('job_type')->pluck('c', 'job_type');
        $counts = ['all' => (int) $byType->sum()];
        foreach (self::TYPES as $t) {
            $counts[$t] = (int) ($byType[$t] ?? 0);
        }
        // Hide empty type chips (the selected one always stays so it can be seen and cleared).
        $types = array_values(array_filter(self::TYPES, fn ($t) => $counts[$t] > 0 || $t === $type));

        // Only card fields: never the poster's contact details.
        $query = (clone $base)->select([
            'id', 'title', 'company_name', 'job_type', 'location', 'salary_min', 'salary_max', 'salary_type',
            'vacancies_count', 'experience_required', 'thumbnail', 'expires_at', 'status', 'created_at',
        ]);

        if ($type !== 'all') {
            $query->where('job_type', $type);
        }

        // Open jobs always before filled ones, whatever the sort.
        $query->orderByRaw("CASE WHEN status = 'filled' THEN 1 ELSE 0 END");

        match ($sort) {
            // Highest pay first, compared as a monthly amount (yearly / 12, hourly x 8 h x 26 days);
            // jobs without a salary go last.
            'salary' => $query
                ->orderByRaw("CASE WHEN salary_type = 'not_disclosed' OR COALESCE(salary_max, salary_min) IS NULL THEN 1 ELSE 0 END")
                ->orderByRaw("COALESCE(salary_max, salary_min) * CASE salary_type WHEN 'yearly' THEN 1 / 12 WHEN 'hourly' THEN 208 ELSE 1 END DESC")
                ->orderByDesc('created_at'),
            // Soonest deadline first; jobs without a closing date go last.
            'closing' => $query
                ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('expires_at')
                ->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };
        $query->orderByDesc('id');

        $jobs = $query->paginate(self::PER_PAGE, ['*'], 'page', $page)->withQueryString();

        if ($wantsCards) {
            if ($jobs->currentPage() > $jobs->lastPage()) {
                return $this->cardsJson('', null);
            }
            $html = $jobs->getCollection()
                ->map(fn ($job) => view('partials.job-card', ['job' => $job, 'lang' => $lang])->render())
                ->implode("\n");

            return $this->cardsJson($html, $jobs->nextPageUrl(), $jobs->lastItem(), $jobs->total());
        }

        // Past the last page (old link, results shrank): go to the last page that has results.
        if ($jobs->currentPage() > $jobs->lastPage()) {
            $last = $jobs->lastPage();
            return redirect()->to($last > 1 ? $jobs->url($last) : $request->fullUrlWithoutQuery('page'));
        }

        $response = response()->view('jobs', [
            'lang'      => $lang,
            'activeNav' => 'jobs',
            'jobs'      => $jobs,
            'total'     => $jobs->total(),
            'counts'    => $counts,
            'q'         => $q,
            'type'      => $type,
            'types'     => $types,
            'sort'      => $sort,
            'sorts'     => self::SORTS,
        ]);

        return $this->rememberSiteLanguage($response, $request, $lang)
            ->header('Vary', 'Accept, X-Requested-With');
    }

    /** JSON for infinite scroll: card <li> markup, the next page URL (null at the end), and counts. */
    private function cardsJson(string $html, ?string $next, ?int $shown = null, ?int $total = null)
    {
        return response()
            ->json(['html' => $html, 'next' => $next, 'shown' => $shown, 'total' => $total])
            ->header('Vary', 'Accept, X-Requested-With')
            ->header('Cache-Control', 'private, no-store');
    }
}
