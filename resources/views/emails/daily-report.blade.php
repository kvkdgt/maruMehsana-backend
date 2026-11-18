@php
    $r = $report;
    $card = 'background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px; text-align:center;';
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Maru Mehsana Report</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family:Arial, Helvetica, sans-serif; color:#0f172a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:24px 12px;">
  <tr><td align="center">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e2e8f0;">
      <tr>
        <td style="background:#0b4f9c; padding:18px 24px; color:#ffffff;">
          <div style="font-size:12px; letter-spacing:0.08em; text-transform:uppercase; opacity:0.85;">Maru Mehsana &middot; Daily Report</div>
          <div style="font-size:20px; font-weight:bold; margin-top:4px;">{{ $r['date']->format('l, d M Y') }}</div>
        </td>
      </tr>

      <tr><td style="padding:20px 24px 4px; font-size:15px; font-weight:bold;">New Users Today</td></tr>
      <tr>
        <td style="padding:8px 24px 16px;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="8">
            <tr>
              <td width="33%" style="{{ $card }}"><div style="font-size:26px; font-weight:bold;">{{ $r['new_users'] }}</div><div style="font-size:12px; color:#64748b;">Total new</div></td>
              <td width="33%" style="{{ $card }}"><div style="font-size:26px; font-weight:bold; color:#15803d;">{{ $r['logged_in_users'] }}</div><div style="font-size:12px; color:#64748b;">Logged in</div></td>
              <td width="33%" style="{{ $card }}"><div style="font-size:26px; font-weight:bold; color:#b45309;">{{ $r['guest_users'] }}</div><div style="font-size:12px; color:#64748b;">Guest</div></td>
            </tr>
          </table>
        </td>
      </tr>

      <tr><td style="padding:8px 24px 4px; font-size:15px; font-weight:bold;">New Jobs Created: {{ $r['jobs']->count() }}</td></tr>
      <tr>
        <td style="padding:4px 24px 16px; font-size:14px;">
          @forelse ($r['jobs'] as $job)
            <div style="padding:8px 0; border-top:1px solid #f1f5f9;">
              <strong>{{ $job->title }}</strong>
              <div style="font-size:12px; color:#64748b;">
                {{ $job->company_name ?: 'N/A' }} &middot; {{ $job->location ?: 'N/A' }} &middot; {{ $job->created_at->timezone('Asia/Kolkata')->format('h:i A') }}
              </div>
            </div>
          @empty
            <div style="color:#94a3b8; font-size:13px;">No new jobs today.</div>
          @endforelse
        </td>
      </tr>

      <tr><td style="padding:8px 24px 4px; font-size:15px; font-weight:bold;">New Enquiries: {{ $r['enquiries']->count() }}</td></tr>
      <tr>
        <td style="padding:4px 24px 24px; font-size:14px;">
          @forelse ($r['enquiries'] as $e)
            <div style="padding:8px 0; border-top:1px solid #f1f5f9;">
              <strong>{{ $e->business_name }}</strong>
              <span style="font-size:11px; font-weight:bold; padding:2px 8px; border-radius:999px; background:{{ $e->source === 'web' ? '#e0f2fe' : '#dcfce7' }}; color:{{ $e->source === 'web' ? '#0e7490' : '#15803d' }};">{{ $e->source === 'web' ? 'Website' : 'App' }}</span>
              <div style="font-size:12px; color:#64748b;">
                {{ $e->owner_name }} &middot; Mobile: {{ $e->mobile_no }} &middot; WhatsApp: {{ $e->whatsapp_no }} &middot; {{ $e->status }} &middot; {{ $e->created_at->timezone('Asia/Kolkata')->format('h:i A') }}
              </div>
            </div>
          @empty
            <div style="color:#94a3b8; font-size:13px;">No new enquiries today.</div>
          @endforelse
        </td>
      </tr>
    </table>
    <div style="font-size:11px; color:#94a3b8; margin-top:12px;">Automatic report, sent every day at 11:59 PM IST.</div>
  </td></tr>
</table>
</body>
</html>
