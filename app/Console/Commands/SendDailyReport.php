<?php

namespace App\Console\Commands;

use App\Mail\DailyReportMail;
use App\Models\AppUser;
use App\Models\BusinessEnquiry;
use App\Models\JobVacancy;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Daily "Maru Mehsana Report" e-mailed at 11:59 PM IST (see Console\Kernel).
 * Covers today's data only: new users (guest vs logged in), new jobs, new enquiries.
 */
class SendDailyReport extends Command
{
    protected $signature = 'report:daily {--date= : Report date (YYYY-MM-DD, IST). Defaults to today} {--print : Print summary instead of e-mailing}';

    protected $description = 'Send the daily Maru Mehsana report (new users, jobs, enquiries) to the admin e-mail';

    public function handle(): int
    {
        $day = $this->option('date')
            ? Carbon::parse($this->option('date'), 'Asia/Kolkata')
            : Carbon::now('Asia/Kolkata');
        $start = $day->copy()->startOfDay();
        $end = $day->copy()->endOfDay();

        $users = AppUser::whereBetween('created_at', [$start, $end])->get();
        $jobs = JobVacancy::whereBetween('created_at', [$start, $end])->orderBy('created_at')->get();
        $enquiries = BusinessEnquiry::whereBetween('created_at', [$start, $end])->orderBy('created_at')->get();

        $report = [
            'date' => $start,
            'new_users' => $users->count(),
            'guest_users' => $users->where('is_login', false)->count(),
            'logged_in_users' => $users->where('is_login', true)->count(),
            'jobs' => $jobs,
            'enquiries' => $enquiries,
        ];

        if ($this->option('print')) {
            $this->info($start->format('d M Y') . " | users {$report['new_users']} (guest {$report['guest_users']}, logged in {$report['logged_in_users']}) | jobs {$jobs->count()} | enquiries {$enquiries->count()}");
            return self::SUCCESS;
        }

        $to = array_values(array_filter(array_map('trim', explode(',', (string) config('services.reports.notify_email')))));
        if (!$to) {
            $this->error('No report recipient. Set REPORT_NOTIFY_EMAIL or LEAD_NOTIFY_EMAIL.');
            return self::FAILURE;
        }

        Mail::to($to)->send(new DailyReportMail($report));
        $this->info('Daily report sent to ' . implode(', ', $to));

        return self::SUCCESS;
    }
}
