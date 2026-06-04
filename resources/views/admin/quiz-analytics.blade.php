@extends('layouts.admin')

@section('title', 'Quiz Analytics')

@section('content')
<style>
  :root {
    --primary-color: #2c3e50;
    --accent-color: #F2652D;
    --text-muted: #64748b;
    --radius: 12px;
  }

  .qa-header {
    background: #fff; border-radius: var(--radius); padding: 25px 30px;
    margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid var(--accent-color);
  }
  .qa-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--primary-color); margin: 0; }
  .qa-header p  { font-size: 0.9rem; color: var(--text-muted); margin: 5px 0 0; }

  .qa-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
  @media (max-width: 900px) { .qa-stats { grid-template-columns: repeat(2, 1fr); } }
  .qa-stat {
    background: #fff; border-radius: var(--radius); padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 15px;
  }
  .qa-stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color:#fff; }
  .qa-stat-val { font-size: 1.6rem; font-weight: 700; color: var(--primary-color); line-height: 1; }
  .qa-stat-tit { font-size: 0.78rem; color: var(--text-muted); font-weight: 600; margin-top: 4px; text-transform: uppercase; }

  .qa-card { background:#fff; border-radius: var(--radius); padding: 22px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 25px; }
  .qa-card-head { display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; flex-wrap:wrap; gap:10px; }
  .qa-card-head h4 { margin:0; color: var(--primary-color); font-size: 1.1rem; font-weight: 700; }
  .qa-grid-2 { display:grid; grid-template-columns: 1fr 1fr; gap: 25px; }
  @media (max-width: 1000px) { .qa-grid-2 { grid-template-columns: 1fr; } }

  .qa-table { width:100%; border-collapse: collapse; }
  .qa-table th { text-align:left; font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); padding:10px 12px; border-bottom:2px solid #eef1f4; }
  .qa-table td { padding:12px; border-bottom:1px solid #f1f3f5; font-size:0.9rem; color:#333; }
  .qa-table tr:hover td { background:#fafbfc; }

  .qa-rank { width:30px; height:30px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem; color:#fff; background:#bcc4cc; }
  .qa-rank.gold { background:#f1c40f; } .qa-rank.silver { background:#95a5a6; } .qa-rank.bronze { background:#cd7f32; }

  .qa-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.78rem; font-weight:600; }
  .qa-badge.correct { background:#e8f8f0; color:#27ae60; }
  .qa-badge.wrong   { background:#fdecea; color:#e74c3c; }

  .qa-empty { text-align:center; color:var(--text-muted); padding:24px; font-size:0.9rem; }
  .qa-select { padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:0.9rem; }
</style>

<div class="qa-header">
  <div>
    <h2><i class="fas fa-chart-line" style="color:var(--accent-color);"></i> Quiz Analytics</h2>
    <p>Player engagement, daily activity and leaderboards</p>
  </div>
  <a href="{{ route('admin.quiz') }}" class="qa-select" style="text-decoration:none; color:var(--primary-color);">
    <i class="fas fa-arrow-left"></i> Back to Quiz
  </a>
</div>

{{-- ── Top stats ─────────────────────────────────────── --}}
<div class="qa-stats">
  <div class="qa-stat">
    <div class="qa-stat-icon" style="background:#3498db;"><i class="fas fa-users"></i></div>
    <div><div class="qa-stat-val">{{ number_format($stats['total_players']) }}</div><div class="qa-stat-tit">Total Players</div></div>
  </div>
  <div class="qa-stat">
    <div class="qa-stat-icon" style="background:#27ae60;"><i class="fas fa-user-clock"></i></div>
    <div><div class="qa-stat-val">{{ number_format($stats['today_players']) }}</div><div class="qa-stat-tit">Today's Players</div></div>
  </div>
  <div class="qa-stat">
    <div class="qa-stat-icon" style="background:#9b59b6;"><i class="fas fa-pen"></i></div>
    <div><div class="qa-stat-val">{{ number_format($stats['today_attempts']) }}</div><div class="qa-stat-tit">Today's Answers</div></div>
  </div>
  <div class="qa-stat">
    <div class="qa-stat-icon" style="background:#F2652D;"><i class="fas fa-layer-group"></i></div>
    <div><div class="qa-stat-val">{{ number_format($stats['total_attempts']) }}</div><div class="qa-stat-tit">Total Answers</div></div>
  </div>
</div>

{{-- ── Who answered today ────────────────────────────── --}}
<div class="qa-card">
  <div class="qa-card-head">
    <h4><i class="fas fa-calendar-day" style="color:#27ae60;"></i> Today's Answers ({{ now()->format('d M Y') }})</h4>
    <span class="qa-badge correct">{{ $todayPlayers->count() }} answer(s)</span>
  </div>
  <div style="overflow-x:auto;">
    <table class="qa-table">
      <thead>
        <tr>
          <th>#</th><th>Player</th><th>Question</th><th>Answer</th><th>Result</th><th>Score</th><th>Time</th><th>Answered At</th>
        </tr>
      </thead>
      <tbody>
        @forelse($todayPlayers as $i => $p)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td style="font-weight:600; color:var(--primary-color);">{{ $p->user->name ?? 'Unknown' }}</td>
          <td>{{ Str::limit($p->question->question ?? '—', 45) }}</td>
          <td>{{ $p->selected_answer }}</td>
          <td>
            @if($p->is_correct)<span class="qa-badge correct">Correct</span>
            @else<span class="qa-badge wrong">Wrong</span>@endif
          </td>
          <td style="font-weight:600;">{{ $p->score }}</td>
          <td>{{ $p->time_taken_seconds ? $p->time_taken_seconds.'s' : '—' }}</td>
          <td>{{ $p->created_at?->format('h:i A') }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="qa-empty">No one has answered the quiz today yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ── Daily activity (last 14 days) ─────────────────── --}}
<div class="qa-card">
  <div class="qa-card-head">
    <h4><i class="fas fa-stream" style="color:#3498db;"></i> Daily Activity — Last 14 Days</h4>
  </div>
  <div style="overflow-x:auto;">
    <table class="qa-table">
      <thead>
        <tr><th>Date</th><th>Players</th><th>Answers</th><th>Correct</th><th>Accuracy</th></tr>
      </thead>
      <tbody>
        @forelse($dailyActivity as $d)
        <tr>
          <td style="font-weight:600;">{{ \Carbon\Carbon::parse($d->quiz_date)->format('d M Y, D') }}</td>
          <td>{{ number_format($d->players) }}</td>
          <td>{{ number_format($d->attempts) }}</td>
          <td>{{ number_format($d->correct) }}</td>
          <td>{{ $d->attempts > 0 ? number_format(($d->correct / $d->attempts) * 100, 1) : 0 }}%</td>
        </tr>
        @empty
        <tr><td colspan="5" class="qa-empty">No activity in the last 14 days.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ── Leaderboards ──────────────────────────────────── --}}
<div class="qa-grid-2">

  {{-- Month-wise leaderboard --}}
  <div class="qa-card">
    <div class="qa-card-head">
      <h4><i class="fas fa-medal" style="color:#f1c40f;"></i> Monthly Leaderboard</h4>
      <form method="GET" action="{{ route('admin.quiz.analytics') }}">
        <select name="month" class="qa-select" onchange="this.form.submit()">
          @if($availableMonths->isEmpty())
            <option value="{{ $month }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}</option>
          @endif
          @foreach($availableMonths as $m)
            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
              {{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y') }}
            </option>
          @endforeach
        </select>
      </form>
    </div>
    <div style="overflow-x:auto;">
      <table class="qa-table">
        <thead><tr><th>Rank</th><th>Player</th><th>Score</th><th>Answers</th><th>Accuracy</th></tr></thead>
        <tbody>
          @forelse($monthlyLeaderboard as $i => $row)
          <tr>
            <td><span class="qa-rank {{ $i==0?'gold':($i==1?'silver':($i==2?'bronze':'')) }}">{{ $i + 1 }}</span></td>
            <td style="font-weight:600; color:var(--primary-color);">{{ $row->user->name ?? 'Unknown' }}</td>
            <td style="font-weight:700; color:var(--accent-color);">{{ number_format($row->total_score) }}</td>
            <td>{{ number_format($row->total_attempts) }}</td>
            <td>{{ $row->total_attempts > 0 ? number_format(($row->correct_answers / $row->total_attempts) * 100, 1) : 0 }}%</td>
          </tr>
          @empty
          <tr><td colspan="5" class="qa-empty">No players for this month.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Overall leaderboard --}}
  <div class="qa-card">
    <div class="qa-card-head">
      <h4><i class="fas fa-trophy" style="color:#e67e22;"></i> Overall Leaderboard</h4>
      <span class="qa-badge" style="background:#f4f6f8; color:var(--text-muted);">All Time</span>
    </div>
    <div style="overflow-x:auto;">
      <table class="qa-table">
        <thead><tr><th>Rank</th><th>Player</th><th>Score</th><th>Answers</th><th>Accuracy</th></tr></thead>
        <tbody>
          @forelse($overallLeaderboard as $i => $row)
          <tr>
            <td><span class="qa-rank {{ $i==0?'gold':($i==1?'silver':($i==2?'bronze':'')) }}">{{ $i + 1 }}</span></td>
            <td style="font-weight:600; color:var(--primary-color);">{{ $row->user->name ?? 'Unknown' }}</td>
            <td style="font-weight:700; color:var(--accent-color);">{{ number_format($row->total_score) }}</td>
            <td>{{ number_format($row->total_attempts) }}</td>
            <td>{{ $row->total_attempts > 0 ? number_format(($row->correct_answers / $row->total_attempts) * 100, 1) : 0 }}%</td>
          </tr>
          @empty
          <tr><td colspan="5" class="qa-empty">No players yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
