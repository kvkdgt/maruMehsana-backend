<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_user_id', 'question_id', 'quiz_date',
        'selected_answer', 'is_correct', 'time_taken_seconds', 'score',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'quiz_date'  => 'date',
    ];

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }

    /**
     * Calculate consecutive correct-answer streak for a user.
     */
    public static function getStreak(int $userId): int
    {
        $dates = self::where('app_user_id', $userId)
            ->where('is_correct', true)
            ->orderByDesc('quiz_date')
            ->pluck('quiz_date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        if (empty($dates)) return 0;

        $streak  = 0;
        $current = Carbon::today();

        foreach ($dates as $date) {
            if ($current->toDateString() === $date) {
                $streak++;
                $current->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Single source of truth for the "this month" leaderboard (admin panel + app).
     * Calendar month in Asia/Kolkata, ranked by total score, ties broken by
     * correct answers then lowest user id so both screens always agree.
     */
    public static function monthlyQuery(?\Carbon\Carbon $month = null)
    {
        $month = ($month ?: now())->copy();

        return self::select(
                'app_user_id',
                DB::raw('SUM(score) as total_score'),
                DB::raw('COUNT(*) as total_attempts'),
                DB::raw('SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) as correct_answers')
            )
            ->whereBetween('quiz_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->groupBy('app_user_id')
            ->orderByDesc('total_score')
            ->orderByDesc('correct_answers')
            ->orderBy('app_user_id');
    }

    /**
     * Top players for the month (default: current month, top 20 - same as admin).
     */
    public static function getLeaderboard(?\Carbon\Carbon $month = null, int $limit = 20)
    {
        return self::monthlyQuery($month)
            ->limit($limit)
            ->with('user:id,name,profile_picture')
            ->get();
    }
}
