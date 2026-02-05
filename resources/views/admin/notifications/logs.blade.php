<!-- resources/views/admin/notifications/logs.blade.php -->
@extends('layouts.admin')

@section('title', 'Notifications Stats')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/pagination.css') }}">

<style>
    /* Custom CSS for Notification Logs Page */
    :root {
        --primary-color: #4f46e5;
        --primary-light: #e0e7ff;
        --success-color: #10b981;
        --success-light: #d1fae5;
        --info-color: #0ea5e9;
        --info-light: #e0f2fe;
        --danger-color: #ef4444;
        --danger-light: #fee2e2;
        --neutral-color: #6b7280;
        --neutral-light: #f3f4f6;
        --text-dark: #1f2937;
    }

    .notification-logs-container {
        padding: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        border-radius: 0.75rem;
        padding: 1.25rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .stat-card-total {
        background-color: var(--neutral-light);
        color: var(--text-dark);
    }

    .stat-card-delivered {
        background-color: var(--success-light);
        color: var(--success-color);
    }

    .stat-card-pending {
        background-color: var(--info-light);
        color: var(--info-color);
    }

    .stat-card-failed {
        background-color: var(--danger-light);
        color: var(--danger-color);
    }

    .stat-card-body {
        text-align: center;
    }

    .stat-title {
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }

    .logs-table-container {
        background-color: white;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .logs-table {
        width: 100%;
        border-collapse: collapse;
    }

    .logs-table th {
        background-color: #f9fafb;
        text-align: left;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--neutral-color);
        border-bottom: 1px solid #e5e7eb;
    }

    .logs-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: background-color 0.2s ease;
    }

    .logs-table tbody tr:last-child {
        border-bottom: none;
    }

    .logs-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .logs-table td {
        padding: 1rem 1.5rem;
        font-size: 0.875rem;
        color: var(--text-dark);
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .badge-success {
        background-color: var(--success-light);
        color: var(--success-color);
    }

    .badge-info {
        background-color: var(--info-light);
        color: var(--info-color);
    }

    .badge-danger {
        background-color: var(--danger-light);
        color: var(--danger-color);
    }

    .empty-data {
        padding: 3rem 0;
        text-align: center;
        color: var(--neutral-color);
    }

    .empty-data-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-data-text {
        font-size: 1rem;
    }

   /* Enhanced Pagination Styles */
.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.pagination li {
    margin: 0;
}

.pagination li a,
.pagination li span {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 2.5rem;
    padding: 0 1rem;
    min-width: 2.5rem;
    text-align: center;
    border: 1px solid #e5e7eb;
    background-color: white;
    color: #1f2937;
    text-decoration: none;
    margin-left: -1px;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
}

.pagination li:first-child a,
.pagination li:first-child span {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

.pagination li:last-child a,
.pagination li:last-child span {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}

.pagination li.active span {
    background-color: #4f46e5;
    color: white;
    border-color: #4f46e5;
    font-weight: 600;
    z-index: 2;
}

.pagination li:not(.active) a:hover {
    background-color: #e0e7ff;
    color: #4f46e5;
    border-color: #e0e7ff;
    z-index: 1;
}

.pagination li.disabled span {
    color: #d1d5db;
    cursor: not-allowed;
    background-color: #f9fafb;
}

/* For smaller screens */
@media (max-width: 480px) {
    .pagination li a,
    .pagination li span {
        padding: 0 0.75rem;
        min-width: 2.25rem;
        height: 2.25rem;
        font-size: 0.875rem;
    }
}

    /* For responsive design */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .logs-table {
            display: block;
            overflow-x: auto;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }
</style>
<div class="notification-logs-container">
    <div class="page-header">
        <h1 class="page-title">Notification Logs: {{ $notification->title }}</h1>
        <a href="{{ route('admin.notifications') }}" class="reset-btn"><i class="fas fa-arrow-left"></i> Back to Notifications</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-card-total">
            <div class="stat-card-body">
                <h5 class="stat-title">Total Sent</h5>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="stat-card stat-card-delivered">
            <div class="stat-card-body">
                <h5 class="stat-title">Delivered</h5>
                <div class="stat-value">{{ $stats['delivered'] }}</div>
            </div>
        </div>
        <div class="stat-card stat-card-pending">
            <div class="stat-card-body">
                <h5 class="stat-title">Pending</h5>
                <div class="stat-value">{{ $stats['sent'] - $stats['delivered'] - $stats['failed'] }}</div>
            </div>
        </div>
        <div class="stat-card stat-card-failed">
            <div class="stat-card-body">
                <h5 class="stat-title">Failed</h5>
                <div class="stat-value">{{ $stats['failed'] }}</div>
            </div>
        </div>
    </div>

    <div class="logs-table-container">
        <table class="logs-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Status</th>
                    <th>Device Type</th>
                    <th>Date & Time</th>
                    <th>Error (if any)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            @if($log->user)
                                {{ $log->user->name ?? 'No Name' }} ({{ $log->user->email ?? $log->user->phone ?? 'No Contact' }})
                            @else
                                User Deleted
                            @endif
                        </td>
                        <td>
                            @if($log->status == 'delivered')
                                <span class="badge badge-success">Delivered</span>
                            @elseif($log->status == 'sent')
                                <span class="badge badge-info">Sent</span>
                            @else
                                <span class="badge badge-danger">Failed</span>
                            @endif
                        </td>
                        <td>{{ ucfirst($log->device_type ?? 'Unknown') }}</td>
                        <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td>{{ $log->error_message ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-data">
                                <div class="empty-data-icon">📭</div>
                                <div class="empty-data-text">No notification logs found.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="pagination-container">
    {{ $logs->appends(request()->query())->links('vendor.pagination.custom') }}
</div>
</div>
@endsection