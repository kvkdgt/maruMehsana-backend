<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobVacancy;
use App\Jobs\SendFcmNotificationJob;
use Illuminate\Support\Facades\Log;

class NotifyJobViewMilestones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:notify-view-milestones {--dry-run : List jobs that would be notified without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifies job posters when their job crosses a views milestone (10+, 30+, 50+, ...)';

    /**
     * Views milestones. The poster gets one notification each time their job's
     * views_count first reaches a value in this list. Extend freely.
     */
    private const MILESTONES = [
        10, 30, 50, 70, 100, 150, 200, 250, 300, 350, 400, 500,
        600, 700, 800, 900, 1000, 1500, 2000, 2500, 3000, 5000, 10000,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Scanning jobs for new views milestones...');

        $dispatched = 0;

        // Prefilter: only jobs that gained views beyond what we last notified,
        // and that have at least reached the first milestone.
        JobVacancy::query()
            ->where('views_count', '>=', self::MILESTONES[0])
            ->whereColumn('views_count', '>', 'last_notified_views_milestone')
            ->with('poster')
            ->chunkById(200, function ($jobs) use (&$dispatched, $dryRun) {
                foreach ($jobs as $job) {
                    $milestone = $this->highestMilestoneReached($job->views_count);

                    // Nothing new crossed since last time.
                    if ($milestone === null || $milestone <= $job->last_notified_views_milestone) {
                        continue;
                    }

                    $poster = $job->poster;
                    if (!$poster) {
                        // Poster gone — still advance the marker so we don't re-scan it forever.
                        $job->update(['last_notified_views_milestone' => $milestone]);
                        continue;
                    }

                    $this->line("Job #{$job->id} \"{$job->title}\" — {$job->views_count} views → milestone {$milestone}+ → user #{$poster->id}");

                    if ($dryRun) {
                        continue;
                    }

                    [$title, $body] = $this->buildMessage($job->title, $milestone);

                    SendFcmNotificationJob::dispatch(
                        $poster->id,
                        $title,
                        $body,
                        $job->thumbnail ?: null, // image
                        null,                    // notificationId (no broadcast record)
                        null,                    // newsId
                        null,                    // newsSlug
                        null,                    // businessId
                        null,                    // touristPlaceId
                        $job->id                 // jobId — deep-link to the job
                    );

                    // Mark this milestone as notified so it never fires twice.
                    $job->update(['last_notified_views_milestone' => $milestone]);
                    $dispatched++;
                }
            });

        if ($dryRun) {
            $this->info('Dry run complete. No notifications sent.');
        } else {
            $this->info("Done. Dispatched {$dispatched} milestone notification(s).");
            Log::info('Job view milestone notifications dispatched', ['count' => $dispatched]);
        }

        return self::SUCCESS;
    }

    /**
     * Highest milestone value that the given views count has reached.
     */
    private function highestMilestoneReached(int $views): ?int
    {
        $reached = null;
        foreach (self::MILESTONES as $m) {
            if ($views >= $m) {
                $reached = $m;
            } else {
                break; // list is ascending
            }
        }
        return $reached;
    }

    /**
     * Build the notification title/body for a milestone (Gujarati — local audience).
     *
     * @return array{0:string,1:string}
     */
    private function buildMessage(string $jobTitle, int $milestone): array
    {
        $title = "🎉 તમારી જોબ લોકપ્રિય થઈ રહી છે!";
        $body  = "\"{$jobTitle}\" ને {$milestone}+ વ્યૂ મળ્યા! 👀 વધુ લોકો સુધી પહોંચાડવા શેર કરો.";

        return [$title, $body];
    }
}
