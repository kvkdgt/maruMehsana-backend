@extends('layouts.admin')

@section('title', 'Job Vacancies')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">
<div class="jobs-container">
    <div class="header-container">
        <div>
            <h3 style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">Job Vacancies</h3>
            <p style="color: #777; font-size: 0.95rem;">Manage all job postings in the application</p>
        </div>
        <div style="background: #fff; padding: 10px 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-briefcase" style="color: #F2652D;"></i>
            <span style="font-weight: 600;">Total Jobs: {{ $jobs->total() }}</span>
        </div>
    </div>

    <div class="filter-section">
        <form action="{{ route('admin.jobs') }}" method="GET">
            <div class="filter-grid">
                <div class="filter-control">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Job title, company, location..." value="{{ request()->search }}" class="filter-input">
                </div>

                <div class="filter-control">
                    <label>Status</label>
                    <select name="status" class="filter-input">
                        <option value="">All Statuses</option>
                        <option value="open" {{ request()->status == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="filled" {{ request()->status == 'filled' ? 'selected' : '' }}>Filled</option>
                        <option value="closed" {{ request()->status == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" class="filter-btn">Apply Filter</button>
                    <a href="{{ route('admin.jobs') }}" class="reset-btn">Reset</a>
                </div>
            </div>
        </form>
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
                    <th>ID</th>
                    <th>Job Information</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Posted By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if(count($jobs) > 0)
                    @foreach($jobs as $job)
                    <tr>
                        <td>#{{ $job->id ?? '?' }}</td>
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 600; color: #2c3e50;">{{ $job->title ?? 'Untitled' }}</span>
                                <span style="font-size: 0.8rem; color: #777;">{{ $job->company_name ?? 'N/A' }} • {{ $job->location ?? 'N/A' }}</span>
                                <span style="font-size: 0.75rem; color: #999;">Posted: {{ $job->created_at ? $job->created_at->format('d M, Y') : 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-premium badge-info" style="text-transform: capitalize;">{{ str_replace('_', ' ', $job->job_type ?? 'N/A') }}</span>
                        </td>
                        <td>
                            @if(($job->status ?? '') == 'open')
                                <span class="badge-premium badge-success">Open</span>
                            @elseif(($job->status ?? '') == 'filled')
                                <span class="badge-premium badge-warning">Filled</span>
                            @else
                                <span class="badge-premium badge-danger">Closed</span>
                            @endif
                            
                            @if(!($job->is_active ?? true))
                                <span class="badge-premium" style="background: #64748b; color: #fff; margin-top: 5px; display: inline-block;">Hidden</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">
                                <strong>{{ optional($job->poster)->name ?? 'Deleted User' }}</strong><br>
                                <span style="color: #777;">ID: {{ $job->posted_by ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <form action="{{ route('admin.jobs.toggle', $job->id ?? 0) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="edit-btn" style="background: {{ ($job->is_active ?? true) ? '#e0f2fe' : '#dcfce7' }}; color: {{ ($job->is_active ?? true) ? '#0369a1' : '#15803d' }}; border: 1px solid {{ ($job->is_active ?? true) ? '#bae6fd' : '#bbf7d0' }};">
                                        <i class="fas {{ ($job->is_active ?? true) ? 'fa-eye-slash' : 'fa-eye' }}"></i> {{ ($job->is_active ?? true) ? 'Hide' : 'Show' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.jobs.delete', $job->id ?? 0) }}" method="POST" onsubmit="return confirm('Permanent delete this job vacancy?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-briefcase" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            No job vacancies found.
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
