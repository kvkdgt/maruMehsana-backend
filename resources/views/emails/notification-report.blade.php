<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notification Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0;
            font-size: 24px;
        }
        .header .type-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .type-started {
            background: #2196F3;
            color: white;
        }
        .type-completed {
            background: #4CAF50;
            color: white;
        }
        .notification-details {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .notification-details h3 {
            margin-top: 0;
            color: #555;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .stat-box.success {
            border-left: 4px solid #4CAF50;
        }
        .stat-box.failed {
            border-left: 4px solid #f44336;
        }
        .stat-box.pending {
            border-left: 4px solid #ff9800;
        }
        .stat-box.total {
            border-left: 4px solid #2196F3;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .success .stat-number { color: #4CAF50; }
        .failed .stat-number { color: #f44336; }
        .pending .stat-number { color: #ff9800; }
        .total .stat-number { color: #2196F3; }
        .footer {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .notification-content {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .notification-content h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 Maru Mehsana</h1>
            <span class="type-badge type-{{ $type }}">
                {{ $type === 'started' ? '🚀 NOTIFICATION STARTED' : '✅ NOTIFICATION COMPLETED' }}
            </span>
        </div>

        <div class="notification-details">
            <h3>📋 Notification Details</h3>
            <div class="detail-row">
                <span class="detail-label">ID:</span>
                <span class="detail-value">#{{ $notification->id }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Title:</span>
                <span class="detail-value">{{ $notification->title }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Type:</span>
                <span class="detail-value">{{ ucfirst($notification->type ?? 'General') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Audience:</span>
                <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $notification->audience)) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created At:</span>
                <span class="detail-value">{{ $notification->created_at->format('d M Y, h:i A') }}</span>
            </div>
            @if($notification->scheduled_at)
            <div class="detail-row">
                <span class="detail-label">Scheduled At:</span>
                <span class="detail-value">{{ $notification->scheduled_at->format('d M Y, h:i A') }}</span>
            </div>
            @endif

            <div class="notification-content">
                <h4>📝 Message Content:</h4>
                <p>{{ $notification->description }}</p>
            </div>
        </div>

        <h3 style="color: #555;">📊 Delivery Statistics</h3>
        <div class="stats-grid">
            <div class="stat-box total">
                <div class="stat-number">{{ $stats['total_users'] ?? 0 }}</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-box success">
                <div class="stat-number">{{ $stats['sent'] ?? 0 }}</div>
                <div class="stat-label">Sent Successfully</div>
            </div>
            <div class="stat-box failed">
                <div class="stat-number">{{ $stats['failed'] ?? 0 }}</div>
                <div class="stat-label">Failed</div>
            </div>
            <div class="stat-box pending">
                <div class="stat-number">{{ $stats['pending'] ?? $stats['jobs_dispatched'] ?? 0 }}</div>
                <div class="stat-label">{{ $type === 'started' ? 'Jobs Dispatched' : 'Pending' }}</div>
            </div>
        </div>

        @if($type === 'completed' && isset($stats['success_rate']))
        <div style="text-align: center; margin: 20px 0;">
            <strong>Success Rate:</strong> 
            <span style="color: {{ $stats['success_rate'] >= 90 ? '#4CAF50' : ($stats['success_rate'] >= 70 ? '#ff9800' : '#f44336') }}; font-size: 18px;">
                {{ $stats['success_rate'] }}%
            </span>
        </div>
        @endif

        <div class="footer">
            <p>This is an automated email from Maru Mehsana Notification System</p>
            <p>© {{ date('Y') }} Maru Mehsana. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

