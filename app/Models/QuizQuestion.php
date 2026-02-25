<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question', 'option_a', 'option_b', 'option_c', 'option_d',
        'correct_answer', 'explanation', 'category', 'difficulty',
        'is_active', 'scheduled_date',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'scheduled_date' => 'date',
    ];

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'question_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get today's question.
     * Priority 1 → specifically scheduled for today
     * Priority 2 → rotate through active questions by day-of-year
     */
    public static function getTodayQuestion(): ?self
    {
        $today = now()->toDateString();

        $scheduled = self::active()->whereDate('scheduled_date', $today)->first();
        if ($scheduled) return $scheduled;

        $ids = self::active()->whereNull('scheduled_date')->pluck('id');
        if ($ids->isEmpty()) return null;

        $index = (int) now()->format('z') % $ids->count();
        return self::find($ids[$index]);
    }
}
