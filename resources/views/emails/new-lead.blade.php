@php
    $e = $enquiry;
    $isWeb = $e->source === 'web';
    $wa = preg_replace('/\D+/', '', (string) $e->whatsapp_no);
    if (strlen($wa) === 10) { $wa = '91' . $wa; }
    $tel = preg_replace('/[^\d+]/', '', (string) $e->mobile_no);
    $received = optional($e->created_at)->timezone('Asia/Kolkata');
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>New lead</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family:Arial, Helvetica, sans-serif; color:#0f172a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:24px 12px;">
  <tr>
    <td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e2e8f0;">
        <tr>
          <td style="background:#0b4f9c; padding:18px 24px; color:#ffffff;">
            <div style="font-size:12px; letter-spacing:0.08em; text-transform:uppercase; opacity:0.85;">Maru Mehsana &middot; New lead</div>
            <div style="font-size:20px; font-weight:bold; margin-top:4px;">{{ $e->business_name }}</div>
          </td>
        </tr>
        <tr>
          <td style="padding:20px 24px 4px;">
            <span style="display:inline-block; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:bold; background:{{ $isWeb ? '#e0f2fe' : '#dcfce7' }}; color:{{ $isWeb ? '#0e7490' : '#15803d' }};">
              {{ $isWeb ? 'From website' : 'From app' }}
            </span>
            @if ($received)
              <span style="font-size:12px; color:#64748b; margin-left:8px;">{{ $received->format('d M Y, h:i A') }} IST</span>
            @endif
          </td>
        </tr>
        <tr>
          <td style="padding:12px 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
              <tr>
                <td style="padding:8px 0; color:#64748b; width:130px;">Business</td>
                <td style="padding:8px 0; font-weight:bold;">{{ $e->business_name }}</td>
              </tr>
              <tr>
                <td style="padding:8px 0; color:#64748b; border-top:1px solid #f1f5f9;">Owner</td>
                <td style="padding:8px 0; font-weight:bold; border-top:1px solid #f1f5f9;">{{ $e->owner_name }}</td>
              </tr>
              <tr>
                <td style="padding:8px 0; color:#64748b; border-top:1px solid #f1f5f9;">Mobile</td>
                <td style="padding:8px 0; font-weight:bold; border-top:1px solid #f1f5f9;"><a href="tel:{{ $tel }}" style="color:#0b4f9c; text-decoration:none;">{{ $e->mobile_no }}</a></td>
              </tr>
              <tr>
                <td style="padding:8px 0; color:#64748b; border-top:1px solid #f1f5f9;">WhatsApp</td>
                <td style="padding:8px 0; font-weight:bold; border-top:1px solid #f1f5f9;">{{ $e->whatsapp_no }}</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:8px 24px 24px;">
            <a href="tel:{{ $tel }}" style="display:inline-block; padding:10px 18px; margin:4px 6px 4px 0; background:#0b4f9c; color:#ffffff; border-radius:8px; font-size:14px; font-weight:bold; text-decoration:none;">Call now</a>
            @if ($wa !== '')
              <a href="https://wa.me/{{ $wa }}" style="display:inline-block; padding:10px 18px; margin:4px 6px 4px 0; background:#16a34a; color:#ffffff; border-radius:8px; font-size:14px; font-weight:bold; text-decoration:none;">WhatsApp</a>
            @endif
            <a href="{{ route('admin.business-enquiry') }}" style="display:inline-block; padding:10px 18px; margin:4px 0; background:#f1f5f9; color:#0f172a; border-radius:8px; font-size:14px; font-weight:bold; text-decoration:none;">Open in admin</a>
          </td>
        </tr>
      </table>
      <div style="font-size:11px; color:#94a3b8; margin-top:12px;">Automatic alert for every new business enquiry.</div>
    </td>
  </tr>
</table>
</body>
</html>
