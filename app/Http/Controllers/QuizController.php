<?php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuizController extends Controller
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
    //  API: GET /api/quiz/today?user_id=xxx
    // ─────────────────────────────────────────────────────────────────────

    public function today(Request $request)
    {
        try {
            $user = $this->resolveLoggedInUser($request);

            if (!$user) {
                return response()->json([
                    'status'  => 'unauthorized',
                    'message' => 'Only registered users can play the daily quiz. Please login to continue.',
                ], 401);
            }

            $today    = now()->toDateString();
            $question = QuizQuestion::getTodayQuestion();

            if (!$question) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No quiz available for today. Check back tomorrow!',
                ], 404);
            }

            $attempt = QuizAttempt::where('app_user_id', $user->id)
                ->whereDate('quiz_date', $today)
                ->first();

            $streak = QuizAttempt::getStreak($user->id);

            $data = [
                'id'             => $question->id,
                'question'       => $question->question,
                'category'       => $question->category,
                'difficulty'     => $question->difficulty,
                'options'        => [
                    'A' => $question->option_a,
                    'B' => $question->option_b,
                    'C' => $question->option_c,
                    'D' => $question->option_d,
                ],
                'quiz_date'      => $today,
                'already_played' => $attempt !== null,
                'streak'         => $streak,
            ];

            if ($attempt) {
                $data['your_answer']    = $attempt->selected_answer;
                $data['correct_answer'] = $question->correct_answer;
                $data['is_correct']     = $attempt->is_correct;
                $data['score_earned']   = $attempt->score;
                $data['explanation']    = $question->explanation;
                $data['time_taken']     = $attempt->time_taken_seconds;
            }

            return response()->json(['status' => 'success', 'data' => $data]);

        } catch (\Throwable $e) {
            Log::error('Quiz today error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  API: POST /api/quiz/submit
    // ─────────────────────────────────────────────────────────────────────

    public function submit(Request $request)
    {
        try {
            $user = $this->resolveLoggedInUser($request);

            if (!$user) {
                return response()->json([
                    'status'  => 'unauthorized',
                    'message' => 'Only registered users can submit quiz answers. Please login to continue.',
                ], 401);
            }

            $request->validate([
                'question_id'        => 'required|integer|exists:quiz_questions,id',
                'selected_answer'    => 'required|in:A,B,C,D',
                'time_taken_seconds' => 'required|integer|min:0|max:60',
            ]);

            $today = now()->toDateString();

            // Prevent double submission
            $existing = QuizAttempt::where('app_user_id', $user->id)
                ->whereDate('quiz_date', $today)
                ->first();

            if ($existing) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'You have already played today\'s quiz. Come back tomorrow! 🌅',
                ], 409);
            }

            $question  = QuizQuestion::findOrFail($request->question_id);
            $isCorrect = strtoupper($request->selected_answer) === $question->correct_answer;

            // Scoring: base 100 + speed bonus (up to 30 pts for answering in < 30 sec)
            $score = 0;
            if ($isCorrect) {
                $speedBonus = max(0, 30 - $request->time_taken_seconds);
                $score      = 100 + $speedBonus;
            }

            QuizAttempt::create([
                'app_user_id'        => $user->id,
                'question_id'        => $question->id,
                'quiz_date'          => $today,
                'selected_answer'    => $request->selected_answer,
                'is_correct'         => $isCorrect,
                'time_taken_seconds' => $request->time_taken_seconds,
                'score'              => $score,
            ]);

            $streak = QuizAttempt::getStreak($user->id);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'is_correct'     => $isCorrect,
                    'correct_answer' => $question->correct_answer,
                    'explanation'    => $question->explanation,
                    'score_earned'   => $score,
                    'streak'         => $streak,
                    'options'        => [
                        'A' => $question->option_a,
                        'B' => $question->option_b,
                        'C' => $question->option_c,
                        'D' => $question->option_d,
                    ],
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('Quiz submit error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  API: GET /api/quiz/stats?user_id=xxx
    // ─────────────────────────────────────────────────────────────────────

    public function stats(Request $request)
    {
        try {
            $user = $this->resolveLoggedInUser($request);

            if (!$user) {
                return response()->json([
                    'status'  => 'unauthorized',
                    'message' => 'Only registered users can view quiz stats. Please login.',
                ], 401);
            }

            $allAttempts   = QuizAttempt::where('app_user_id', $user->id)->orderByDesc('quiz_date')->get();
            $totalAttempts = $allAttempts->count();
            $totalCorrect  = $allAttempts->where('is_correct', true)->count();
            $totalScore    = $allAttempts->sum('score');
            $streak        = QuizAttempt::getStreak($user->id);

            // Last 7 days history
            $last7 = [];
            for ($i = 6; $i >= 0; $i--) {
                $date    = Carbon::today()->subDays($i)->toDateString();
                $attempt = $allAttempts->first(fn($a) => $a->quiz_date->toDateString() === $date);
                $last7[] = [
                    'date'       => $date,
                    'played'     => $attempt !== null,
                    'is_correct' => $attempt?->is_correct ?? false,
                    'score'      => $attempt?->score ?? 0,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'user_name'      => $user->name,
                    'streak'         => $streak,
                    'total_score'    => $totalScore,
                    'total_attempts' => $totalAttempts,
                    'total_correct'  => $totalCorrect,
                    'accuracy'       => $totalAttempts > 0 ? round(($totalCorrect / $totalAttempts) * 100) : 0,
                    'last_7_days'    => $last7,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('Quiz stats error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  API: GET /api/quiz/leaderboard?user_id=xxx
    // ─────────────────────────────────────────────────────────────────────

    public function leaderboard(Request $request)
    {
        try {
            $leaderboard = QuizAttempt::getLeaderboard();

            $userId = $request->input('user_id');
            $myRank = null;

            if ($userId) {
                $ranked = QuizAttempt::select('app_user_id', DB::raw('SUM(score) as total_score'))
                    ->where('quiz_date', '>=', now()->subDays(30)->toDateString())
                    ->groupBy('app_user_id')
                    ->orderByDesc('total_score')
                    ->pluck('app_user_id')
                    ->toArray();

                $pos = array_search((int) $userId, $ranked);
                $myRank = $pos !== false ? $pos + 1 : null;
            }

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'leaderboard' => $leaderboard->map(function ($item, $index) {
                        return [
                            'rank'            => $index + 1,
                            'name'            => $item->user?->name ?? 'Player',
                            'total_score'     => $item->total_score,
                            'total_attempts'  => $item->total_attempts,
                            'correct_answers' => $item->correct_answers,
                        ];
                    }),
                    'my_rank' => $myRank,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('Quiz leaderboard error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  ADMIN: List all questions
    // ─────────────────────────────────────────────────────────────────────

    public function adminIndex(Request $request)
    {
        $query = QuizQuestion::query();

        if ($s = $request->search) {
            $query->where('question', 'like', "%{$s}%");
        }
        if ($c = $request->category) {
            $query->where('category', $c);
        }
        if ($d = $request->difficulty) {
            $query->where('difficulty', $d);
        }

        $questions = $query->withCount('attempts')
            ->addSelect([
                'correct_count' => QuizAttempt::selectRaw('count(*)')
                    ->whereColumn('question_id', 'quiz_questions.id')
                    ->where('is_correct', true)
            ])
            ->orderByDesc('created_at')
            ->paginate(15);
            
        // Calculate percentage
        $questions->getCollection()->each(function($q) {
            $q->correct_percentage = $q->attempts_count > 0 
                ? ($q->correct_count / $q->attempts_count) * 100 
                : 0;
        });

        $stats = [
            'total'   => QuizQuestion::count(),
            'active'  => QuizQuestion::where('is_active', true)->count(),
            'attempts'=> QuizAttempt::count(),
            'correct' => QuizAttempt::where('is_correct', true)->count(),
        ];

        return view('admin.quiz', compact('questions', 'stats'));
    }

    public function adminStore(Request $request)
    {
        $request->validate([
            'question'       => 'required|string|max:500',
            'option_a'       => 'required|string|max:200',
            'option_b'       => 'required|string|max:200',
            'option_c'       => 'required|string|max:200',
            'option_d'       => 'required|string|max:200',
            'correct_answer' => 'required|in:A,B,C,D',
            'explanation'    => 'nullable|string|max:1000',
            'category'       => 'required|in:general,history,culture,food,nature,geography',
            'difficulty'     => 'required|in:easy,medium,hard',
            'scheduled_date' => 'nullable|date',
        ]);

        QuizQuestion::create($request->only([
            'question', 'option_a', 'option_b', 'option_c', 'option_d',
            'correct_answer', 'explanation', 'category', 'difficulty', 'scheduled_date',
        ]));

        return redirect()->route('admin.quiz')->with('success', 'Quiz question added successfully!');
    }

    public function adminUpdate(Request $request, $id)
    {
        $request->validate([
            'question'       => 'required|string|max:500',
            'option_a'       => 'required|string|max:200',
            'option_b'       => 'required|string|max:200',
            'option_c'       => 'required|string|max:200',
            'option_d'       => 'required|string|max:200',
            'correct_answer' => 'required|in:A,B,C,D',
            'explanation'    => 'nullable|string|max:1000',
            'category'       => 'required|in:general,history,culture,food,nature,geography',
            'difficulty'     => 'required|in:easy,medium,hard',
            'scheduled_date' => 'nullable|date',
        ]);

        QuizQuestion::findOrFail($id)->update($request->only([
            'question', 'option_a', 'option_b', 'option_c', 'option_d',
            'correct_answer', 'explanation', 'category', 'difficulty', 'scheduled_date',
        ]));

        return redirect()->route('admin.quiz')->with('success', 'Quiz question updated successfully!');
    }

    public function adminToggle($id)
    {
        $q = QuizQuestion::findOrFail($id);
        $q->is_active = !$q->is_active;
        $q->save();
        return redirect()->route('admin.quiz')->with('success', 'Question status updated.');
    }

    public function adminDestroy($id)
    {
        QuizQuestion::findOrFail($id)->delete();
        return redirect()->route('admin.quiz')->with('success', 'Question deleted.');
    }

    public function adminGetQuestion($id)
    {
        return response()->json(QuizQuestion::findOrFail($id));
    }

    public function adminBulkDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || !is_array($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No questions selected.'], 400);
        }

        QuizQuestion::whereIn('id', $ids)->delete();
        return response()->json(['status' => 'success', 'message' => count($ids) . ' questions deleted successfully.']);
    }

    public function adminBulkImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header
        $header = fgetcsv($handle);
        
        $count = 0;
        $errors = [];
        $rowIdx = 2; // Row 1 is header

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 7) {
                $errors[] = "Row {$rowIdx}: Missing required columns.";
                $rowIdx++;
                continue;
            }

            try {
                QuizQuestion::create([
                    'question'       => $row[0],
                    'option_a'       => $row[1],
                    'option_b'       => $row[2],
                    'option_c'       => $row[3],
                    'option_d'       => $row[4],
                    'correct_answer' => strtoupper($row[5]),
                    'category'       => $row[6] ?? 'general',
                    'difficulty'     => $row[7] ?? 'medium',
                    'explanation'    => $row[8] ?? null,
                    'is_active'      => true,
                ]);
                $count++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowIdx}: " . $e->getMessage();
            }
            $rowIdx++;
        }
        fclose($handle);

        $msg = "Successfully imported {$count} questions.";
        if (count($errors) > 0) {
            $msg .= " " . count($errors) . " errors found.";
        }

        return redirect()->route('admin.quiz')->with('success', $msg);
    }

    public function adminDownloadSampleCSV()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="maru_mehsana_quiz_template.csv"',
        ];

        $columns = ['Question', 'Option A', 'Option B', 'Option C', 'Option D', 'Correct Answer', 'Category', 'Difficulty', 'Explanation'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            // Add sample row
            fputcsv($file, [
                'In which year was the Sun Temple of Modhera built?',
                '1024-25 AD',
                '1026-27 AD',
                '1028-29 AD',
                '1030-31 AD',
                'B',
                'history',
                'medium',
                'The Sun Temple was built during the reign of Bhima I of the Chaulukya dynasty.'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
