@extends('layouts.admin')

@section('title', 'Daily Quiz Management')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<style>
  /* ─── Adjusting to Admin Theme ───────────────── */
  :root {
    --primary-color: #2c3e50;
    --accent-color: #F2652D;
    --accent-hover: #e65a25;
    --bg-light: #f8fafc;
    --text-muted: #64748b;
    --radius: 12px;
  }

  /* ─── Header Section ─────────────────────────── */
  .quiz-header-card {
    background: #fff;
    border-radius: var(--radius);
    padding: 25px 30px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border-left: 5px solid var(--accent-color);
  }
  .quiz-header-card h2 { font-size: 1.5rem; font-weight: 700; color: var(--primary-color); margin: 0; }
  .quiz-header-card p  { font-size: 0.9rem; color: var(--text-muted); margin: 5px 0 0; }

  /* ─── Stats Section ──────────────────────────── */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 25px;
  }
  .stat-card {
    background: #fff;
    border-radius: var(--radius);
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.2s;
  }
  .stat-card:hover { transform: translateY(-3px); }
  .stat-icon-box {
    width: 50px; height: 50px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
  }
  .stat-val { font-size: 1.6rem; font-weight: 700; color: var(--primary-color); line-height: 1; }
  .stat-tit { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; margin-top: 4px; text-transform: uppercase; }

  /* ─── Filter Section ─────────────────────────── */
  .quiz-filter-box {
    background: #fff;
    border-radius: var(--radius);
    padding: 20px 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  }
  .filter-row { display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; }
  .filter-item { display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 180px; }
  .filter-item label { font-size: 0.85rem; font-weight: 600; color: #444; }
  .filter-input-styled {
    padding: 10px 15px; border: 1px solid #ddd;
    border-radius: 8px; font-size: 0.9rem; color: #333;
    outline: none; transition: border-color 0.2s;
  }
  .filter-input-styled:focus { border-color: var(--accent-color); }

  /* ─── Question Cards ─────────────────────────── */
  .questions-container { display: flex; flex-direction: column; gap: 15px; }
  .question-item {
    background: #fff;
    border-radius: var(--radius);
    padding: 20px 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    border-left: 4px solid #eee;
    transition: all 0.2s;
  }
  .question-item.active-item { border-left-color: #10b981; }
  .question-item:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.08); }

  .q-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
  .q-badges { display: flex; gap: 8px; align-items: center; }
  .q-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
  .badge-cat { background: #eff6ff; color: #3b82f6; }
  .badge-easy { background: #ecfdf5; color: #10b981; }
  .badge-medium { background: #fffbeb; color: #f59e0b; }
  .badge-hard { background: #fef2f2; color: #ef4444; }
  
  .q-title { font-size: 1.05rem; font-weight: 600; color: var(--primary-color); line-height: 1.5; margin-bottom: 15px; }
  .q-options-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 15px; }
  .option-box {
    padding: 8px 12px; border-radius: 8px; font-size: 0.85rem;
    background: #f8fafc; color: #475569; display: flex; align-items: center; gap: 8px;
    border: 1px solid transparent;
  }
  .option-box.is-correct { background: #f0fdf4; border-color: #86efac; color: #166534; font-weight: 600; }
  .opt-idx {
    width: 22px; height: 22px; border-radius: 50%;
    background: #e2e8f0; display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 800; color: #64748b;
  }
  .is-correct .opt-idx { background: #10b981; color: #fff; }

  .q-bottom { border-top: 1px solid #f1f5f9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; }
  .q-info { font-size: 0.8rem; color: #94a3b8; display: flex; align-items: center; gap: 15px; }

  /* ─── CUSTOM MODAL (Aligned with Theme) ───────── */
  .modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
    z-index: 10000; align-items: center; justify-content: center; padding: 20px;
    animation: fadeIn 0.3s ease;
  }
  .modal-overlay.open { display: flex; }
  .custom-modal {
    background: #fff; width: 100%; max-width: 850px;
    border-radius: 15px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.25);
    animation: slideUp 0.3s ease-out;
    max-height: 90vh; display: flex; flex-direction: column;
  }
  #quizForm { display: flex; flex-direction: column; flex: 1; overflow: hidden; }
  @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
  @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

  .c-modal-header {
    background: var(--primary-color);
    padding: 20px 30px; color: #fff;
    display: flex; justify-content: space-between; align-items: center;
    flex-shrink: 0;
  }
  .c-modal-header h3 { margin: 0; font-size: 1.25rem; font-weight: 700; }
  .close-modal-btn { background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer; opacity: 0.7; transition: 0.2s; }
  .close-modal-btn:hover { opacity: 1; transform: scale(1.1); }

  .c-modal-body { padding: 30px; overflow-y: auto; flex: 1; }
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  .form-full { grid-column: span 2; }
  
  .c-form-group { margin-bottom: 20px; }
  .c-form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 8px; }
  .c-input {
    width: 100%; padding: 12px 15px; border: 1px solid #ddd;
    border-radius: 8px; font-size: 0.95rem; color: #333; outline: none; transition: all 0.2s;
    box-sizing: border-box;
  }
  .c-input:focus { border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(242, 101, 45, 0.1); }
  
  .correct-picker { display: flex; gap: 10px; }
  .correct-opt-radio { display: none; }
  .correct-opt-label {
    flex: 1; text-align: center; padding: 10px;
    border: 2px solid #e2e8f0; border-radius: 8px;
    font-weight: 700; cursor: pointer; transition: all 0.2s;
  }
  .correct-opt-radio:checked + .correct-opt-label {
    background: #f0fdf4; border-color: #10b981; color: #10b981;
  }

  .modal-actions {
    padding: 20px 30px; border-top: 1px solid #eee;
    display: flex; justify-content: flex-end; gap: 15px;
    flex-shrink: 0; background: #fff;
  }
  .btn-submit {
    background: var(--accent-color); color: #fff;
    border: none; padding: 12px 35px; border-radius: 8px;
    font-weight: 700; font-size: 0.95rem; cursor: pointer;
    box-shadow: 0 4px 10px rgba(242, 101, 45, 0.2);
    transition: 0.2s;
  }
  .btn-submit:hover { background: var(--accent-hover); transform: translateY(-2px); }
  .btn-cancel {
    background: #f1f5f9; color: #64748b;
    border: none; padding: 12px 25px; border-radius: 8px;
    font-weight: 600; cursor: pointer;
  }

  /* ─── Toggle Button ──────────────────────────── */
  .toggle-status { border: none; padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 5px; }
  .toggle-on { background: #dcfce7; color: #15803d; }
  .toggle-off { background: #f1f5f9; color: #64748b; }
</style>

<div class="tourist-container">
    {{-- Header Content --}}
    <div class="quiz-header-card">
        <div>
            <h2>Daily Quiz Management</h2>
            <p>Engage your users with daily trivia about Mehsana city and culture.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn-cancel" onclick="document.getElementById('importModal').classList.add('open')" style="background: #fff; border: 1px solid #ddd; height: 100%; padding: 10px 20px;">
                <i class="fas fa-file-import"></i> Import CSV
            </button>
            <button class="btn-add-premium" id="openAdd">
                <i class="fas fa-plus-circle"></i> Add Question
            </button>
        </div>
    </div>

    {{-- Bulk Actions Bar (Hidden by default) --}}
    <div id="bulkActionsBar" style="display: none; background: #2c3e50; color: #fff; padding: 15px 25px; border-radius: 12px; margin-bottom: 20px; align-items: center; justify-content: space-between; animation: slideUp 0.3s ease;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-check-square" style="color: var(--accent-color); font-size: 1.2rem;"></i>
            <span id="selectedCount" style="font-weight: 700;">0 Questions Selected</span>
        </div>
        <div style="display: flex; gap: 12px;">
            <button onclick="confirmBulkDelete()" class="delete-btn" style="background: #ef4444; color: #fff; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 700;">
                <i class="fas fa-trash-alt"></i> Delete Selected
            </button>
            <button onclick="deselectAll()" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 8px; cursor: pointer;">Cancel</button>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon-box" style="background: rgba(44, 62, 80, 0.1); color: var(--primary-color);">
                <i class="fas fa-database"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['total'] }}</div>
                <div class="stat-tit">Total Questions</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-box" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['active'] }}</div>
                <div class="stat-tit">Active Now</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-box" style="background: rgba(242, 101, 45, 0.1); color: var(--accent-color);">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['attempts'] }}</div>
                <div class="stat-tit">Total Plays</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-box" style="background: rgba(251, 191, 36, 0.1); color: #d97706;">
                <i class="fas fa-star"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['correct'] }}</div>
                <div class="stat-tit">Avg. Correct</div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="quiz-filter-box">
        <form action="{{ route('admin.quiz') }}" method="GET" class="filter-row">
            <div class="filter-item">
                <label>Search Question</label>
                <input type="text" name="search" class="filter-input-styled" placeholder="Keywords..." value="{{ request('search') }}">
            </div>
            <div class="filter-item">
                <label>Category</label>
                <select name="category" class="filter-input-styled">
                    <option value="">All Categories</option>
                    @foreach(['general','history','culture','food','nature','geography'] as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item">
                <label>Difficulty Level</label>
                <select name="difficulty" class="filter-input-styled">
                    <option value="">Any Difficulty</option>
                    <option value="easy"   {{ request('difficulty') == 'easy'   ? 'selected' : '' }}>Easy</option>
                    <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="hard"   {{ request('difficulty') == 'hard'   ? 'selected' : '' }}>Hard</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="filter-btn" style="padding: 10px 25px !important;">Apply</button>
                <a href="{{ route('admin.quiz') }}" class="reset-btn" style="padding: 9px 20px !important;">Reset</a>
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 25px; background: #ecfdf5; border-color: #10b981; color: #065f46; display: flex; align-items: center; gap: 12px; padding: 15px 25px; border-radius: 10px; border-left: 5px solid #10b981;">
        <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
        <span style="font-weight: 600;">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Questions Display --}}
    <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px; padding-left: 10px;">
        <input type="checkbox" id="selectAll" style="width: 18px; height: 18px; cursor: pointer;">
        <label for="selectAll" style="font-weight: 700; color: var(--primary-color); cursor: pointer; font-size: 0.9rem;">Select All Questions</label>
    </div>

    <div class="questions-container">
        @forelse($questions as $q)
        <div class="question-item {{ $q->is_active ? 'active-item' : '' }}" data-id="{{ $q->id }}">
            <div class="q-top">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <input type="checkbox" class="question-checkbox" value="{{ $q->id }}" style="width: 18px; height: 18px; cursor: pointer;">
                    <div class="q-badges">
                        <span class="q-badge badge-cat">{{ $q->category }}</span>
                        <span class="q-badge badge-{{ $q->difficulty }}">{{ $q->difficulty }}</span>
                        @if($q->scheduled_date)
                            <span class="q-badge" style="background:#f1f5f9; color:#64748b;"><i class="fas fa-clock"></i> {{ $q->scheduled_date->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button class="edit-btn" onclick="openEdit({{ $q->id }})"><i class="fas fa-pencil-alt"></i> Edit</button>
                    <form action="{{ route('admin.quiz.delete', $q->id) }}" method="POST" onsubmit="return confirm('Really delete recursive question?')" style="display:inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="delete-btn" style="padding: 7px 14px; height: auto;"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            
            <div class="q-title">{{ $q->question }}</div>

            <div class="q-options-row">
                @foreach(['A','B','C','D'] as $opt)
                    @php $f = 'option_' . strtolower($opt); @endphp
                    <div class="option-box {{ $q->correct_answer == $opt ? 'is-correct' : '' }}">
                        <div class="opt-idx">{{ $opt }}</div>
                        <span>{{ $q->$f }}</span>
                        @if($q->correct_answer == $opt) <i class="fas fa-check-circle" style="margin-left:auto"></i> @endif
                    </div>
                @endforeach
            </div>

            <div class="q-bottom">
                <div class="q-info">
                    <span><i class="fas fa-play" style="color:#cbd5e1"></i> {{ $q->attempts_count ?? 0 }} Plays</span>
                    <span><i class="fas fa-check" style="color:#cbd5e1"></i> {{ round($q->correct_percentage ?? 0) }}% Success</span>
                </div>
                <form action="{{ route('admin.quiz.toggle', $q->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="toggle-status {{ $q->is_active ? 'toggle-on' : 'toggle-off' }}">
                        <i class="fas fa-{{ $q->is_active ? 'eye' : 'eye-slash' }}"></i>
                        {{ $q->is_active ? 'Published' : 'Draft' }}
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align:center; padding: 100px 20px; color: #94a3b8;">
            <i class="fas fa-puzzle-piece" style="font-size: 4rem; opacity: 0.2; margin-bottom: 20px;"></i>
            <h3 style="margin: 0;">No Questions Found</h3>
            <p>Ready to start the daily trivia? Click "Add Question" above.</p>
        </div>
        @endforelse
    </div>

    <div class="pagination-container" style="margin-top: 30px;">
        {{ $questions->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>
</div>

{{-- ─── BULK IMPORT MODAL ─────────────────────────── --}}
<div class="modal-overlay" id="importModal">
    <div class="custom-modal" style="max-width: 500px;">
        <div class="c-modal-header">
            <h3>Import Questions via CSV</h3>
            <button class="close-modal-btn" onclick="document.getElementById('importModal').classList.remove('open')">&times;</button>
        </div>
        <form action="{{ route('admin.quiz.bulk-import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="c-modal-body">
                <div style="background: #fff8eb; border: 1px solid #ffe5b5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: flex; gap: 10px; color: #856404; font-size: 0.85rem;">
                        <i class="fas fa-info-circle" style="margin-top: 3px;"></i>
                        <div>
                            <strong>CSV Format Guide:</strong><br>
                            Column 1: Question Text<br>
                            Column 2-5: Options A, B, C, D<br>
                            Column 6: Correct Answer (A, B, C, or D)<br>
                            Column 7: Category<br>
                            Column 8: Difficulty (easy, medium, hard)<br>
                            Column 9: Explanation (Optional)
                        </div>
                    </div>
                </div>
                
                <div class="c-form-group">
                    <label>Select CSV File *</label>
                    <input type="file" name="csv_file" class="c-input" accept=".csv" required style="padding: 10px;">
                    <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 10px;">Don't have a template? <a href="{{ route('admin.quiz.sample-csv') }}" style="color: var(--accent-color); font-weight: 600;">Download Sample CSV</a></p>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="document.getElementById('importModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn-submit">Start Import</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── ADD/EDIT OVERLAY MODAL ───────────────────────── --}}
<div class="modal-overlay" id="quizOverlay">
    <div class="custom-modal">
        <div class="c-modal-header">
            <h3 id="modalHead">Add New Quiz Question</h3>
            <button class="close-modal-btn" id="close">&times;</button>
        </div>
        <form id="quizForm" action="{{ route('admin.quiz.store') }}" method="POST">
            @csrf
            <div class="c-modal-body">
                <input type="hidden" id="q_id">
                
                <div class="c-form-group">
                    <label>The Question (Keep it concise and interesting) *</label>
                    <textarea name="question" id="m_question" class="c-input" rows="3" required placeholder="e.g. In which Year modhera sun temple was built?"></textarea>
                </div>

                <div class="form-grid">
                    <div class="c-form-group">
                        <label>Option A *</label>
                        <input type="text" name="option_a" id="m_a" class="c-input" required placeholder="First choice">
                    </div>
                    <div class="c-form-group">
                        <label>Option B *</label>
                        <input type="text" name="option_b" id="m_b" class="c-input" required placeholder="Second choice">
                    </div>
                    <div class="c-form-group">
                        <label>Option C *</label>
                        <input type="text" name="option_c" id="m_c" class="c-input" required placeholder="Third choice">
                    </div>
                    <div class="c-form-group">
                        <label>Option D *</label>
                        <input type="text" name="option_d" id="m_d" class="c-input" required placeholder="Fourth choice">
                    </div>
                </div>

                <div class="c-form-group">
                    <label>Select Correct Answer *</label>
                    <div class="correct-picker">
                        @foreach(['A','B','C','D'] as $x)
                        <input type="radio" name="correct_answer" value="{{ $x }}" id="rad_{{ $x }}" class="correct-opt-radio" {{ $x == 'A' ? 'checked' : '' }}>
                        <label for="rad_{{ $x }}" class="correct-opt-label">{{ $x }}</label>
                        @endforeach
                    </div>
                </div>

                <div class="form-grid">
                    <div class="c-form-group">
                        <label>Category *</label>
                        <select name="category" id="m_cat" class="c-input" required>
                            @foreach(['general','history','culture','food','nature','geography'] as $c)
                                <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="c-form-group">
                        <label>Difficulty *</label>
                        <select name="difficulty" id="m_diff" class="c-input" required>
                            <option value="easy">Easy</option>
                            <option value="medium" selected>Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="c-form-group">
                        <label>Schedule for Specific Date (Optional)</label>
                        <input type="date" name="scheduled_date" id="m_date" class="c-input">
                    </div>
                    <div class="c-form-group">
                        <label>Is Published?</label>
                        <select name="is_active" id="m_active" class="c-input">
                            <option value="1">Yes, Active</option>
                            <option value="0">No, Draft</option>
                        </select>
                    </div>
                </div>

                <div class="c-form-group" style="margin-bottom:0">
                    <label>Add an Explanation (Shown to user after answering)</label>
                    <textarea name="explanation" id="m_explain" class="c-input" rows="2" placeholder="Tell them a fun fact about this answer..."></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" id="cancel">Cancel</button>
                <button type="submit" class="btn-submit" id="submitBtn">Save Question</button>
            </div>
        </form>
    </div>
</div>

<script>
    const overlay = document.getElementById('quizOverlay');
    const qForm = document.getElementById('quizForm');
    
    function showModal() { overlay.classList.add('open'); }
    function hideModal() { 
        overlay.classList.remove('open'); 
        qForm.reset(); 
        document.getElementById('q_id').value = '';
        document.getElementById('modalHead').innerText = 'Add New Quiz Question';
        qForm.action = "{{ route('admin.quiz.store') }}";
        document.getElementById('submitBtn').innerText = 'Save Question';
    }

    document.getElementById('openAdd').onclick = showModal;
    document.getElementById('close').onclick = hideModal;
    document.getElementById('cancel').onclick = hideModal;

    function openEdit(id) {
        document.getElementById('modalHead').innerText = 'Edit Quiz Question';
        document.getElementById('submitBtn').innerText = 'Update Question';
        document.getElementById('q_id').value = id;
        qForm.action = `/admin/quiz/update/${id}`;
        
        // Disable UI while fetching
        overlay.classList.add('open');
        
        fetch(`/admin/quiz/get/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('m_question').value = data.question;
                document.getElementById('m_a').value = data.option_a;
                document.getElementById('m_b').value = data.option_b;
                document.getElementById('m_c').value = data.option_c;
                document.getElementById('m_d').value = data.option_d;
                document.getElementById('m_cat').value = data.category;
                document.getElementById('m_diff').value = data.difficulty;
                document.getElementById('m_date').value = data.scheduled_date || '';
                document.getElementById('m_active').value = data.is_active ? "1" : "0";
                document.getElementById('m_explain').value = data.explanation || '';
                
                // Select correct radio
                document.getElementById(`rad_${data.correct_answer}`).checked = true;
            })
            .catch(err => {
                alert("Error loading data");
                hideModal();
            });
    }

    // Close on backdrop click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) hideModal();
    });

    // ─── Bulk Operations Logic ──────────────────────
    const selectAll = document.getElementById('selectAll');
    const questionCheckboxes = document.querySelectorAll('.question-checkbox');
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCountText = document.getElementById('selectedCount');

    function updateBulkBar() {
        const selected = document.querySelectorAll('.question-checkbox:checked');
        const count = selected.length;
        
        if (count > 0) {
            bulkBar.style.display = 'flex';
            selectedCountText.innerText = `${count} Question${count > 1 ? 's' : ''} Selected`;
        } else {
            bulkBar.style.display = 'none';
        }
        
        selectAll.checked = (count === questionCheckboxes.length && count > 0);
    }

    selectAll.addEventListener('change', function() {
        questionCheckboxes.forEach(cb => {
            cb.checked = selectAll.checked;
        });
        updateBulkBar();
    });

    questionCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    function deselectAll() {
        questionCheckboxes.forEach(cb => cb.checked = false);
        selectAll.checked = false;
        updateBulkBar();
    }

    function confirmBulkDelete() {
        const selected = Array.from(document.querySelectorAll('.question-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) return;

        if (confirm(`Are you sure you want to delete ${selected.length} questions? This action cannot be undone.`)) {
            fetch("{{ route('admin.quiz.bulk-delete') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ ids: selected })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message || "Something went wrong");
                }
            })
            .catch(err => alert("Error: " + err));
        }
    }
</script>

@endsection
