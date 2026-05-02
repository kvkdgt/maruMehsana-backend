@extends('layouts.admin')

@section('title', 'Reported Jobs')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">
<div class="jobs-container">
    <div class="header-container">
        <div>
            <h3 style="font-size: 1.5rem; font-weight: 700; color: #e74c3c; margin-bottom: 5px;">Reported Job Vacancies</h3>
            <p style="color: #777; font-size: 0.95rem;">Review and manage jobs reported as spam or fake</p>
        </div>
        <div style="background: #fff; padding: 10px 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-flag" style="color: #e74c3c;"></i>
            <span style="font-weight: 600;">Reported Items: {{ $jobs->total() }}</span>
        </div>
    </div>

    @if (session('success'))
    <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
        <div><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
    </div>
    @endif

    <div class="premium-table-container">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Reports</th>
                    <th>Job Information</th>
                    <th>Poster</th>
                    <th>Report Summary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if(count($jobs) > 0)
                    @foreach($jobs as $job)
                    <tr>
                        <td>
                            <span class="badge-premium badge-danger" style="font-size: 1rem; padding: 8px 15px;">
                                <i class="fas fa-exclamation-triangle"></i> {{ $job->reports_count }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 600; color: #2c3e50;">{{ $job->title }}</span>
                                <span style="font-size: 0.8rem; color: #777;">{{ $job->company_name }}</span>
                                <span style="font-size: 0.75rem; color: {{ $job->is_active ? '#2ecc71' : '#95a5a6' }};">
                                    Status: {{ $job->is_active ? 'Visible' : 'Hidden' }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">
                                <strong>{{ $job->poster->name ?? 'N/A' }}</strong><br>
                                <span style="color: #777;">{{ $job->poster->email ?? '' }}</span>
                            </div>
                        </td>
                        <td>
                            <div style="max-width: 300px;">
                                @if(isset($job->reports) && count($job->reports) > 0)
                                    @foreach($job->reports->take(3) as $report)
                                        <div style="margin-bottom: 8px; padding: 8px; background: #f9fafb; border-radius: 6px; border-left: 3px solid #e74c3c;">
                                            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #c0392b;">{{ str_replace('_', ' ', $report->reason ?? 'Spam') }}</span>
                                            <p style="font-size: 0.8rem; margin: 2px 0; color: #555;">{{ $report->description ?? 'No description' }}</p>
                                            <span style="font-size: 0.7rem; color: #999;">By: {{ optional($report->reporter)->name ?? 'User #'.($report->reported_by ?? '?') }}</span>
                                        </div>
                                    @endforeach
                                    @if(($job->reports_count ?? 0) > 3)
                                        <span style="font-size: 0.8rem; color: #777; font-style: italic;">+ {{ ($job->reports_count ?? 0) - 3 }} more reports...</span>
                                    @endif
                                @else
                                    <span style="font-size: 0.8rem; color: #999;">No detailed report logs</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @if($job->is_active)
                                    <form action="{{ route('admin.jobs.toggle', $job->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="edit-btn" style="width: 100%; background: #fffde7; color: #856404; border: 1px solid #ffeeba;">
                                            <i class="fas fa-eye-slash"></i> Hide Job
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.jobs.delete', $job->id) }}" method="POST" onsubmit="return confirm('DANGEROUS: Permanent delete this job vacancy and all its reports?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn" style="width: 100%;">
                                        <i class="fas fa-trash-alt"></i> Delete Forever
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #2ecc71;">
                            <i class="fas fa-shield-alt" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            Great! No reported jobs at the moment.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div class="pagination-container">
            {{ $jobs->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection
