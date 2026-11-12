<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessEnquiry extends Model
{
    use HasFactory;
    protected $fillable = ['business_name', 'owner_name', 'mobile_no', 'whatsapp_no', 'status', 'source'];

    protected $attributes = [
        'status' => 'Pending',
        'source' => 'app',
    ];

    /**
     * Every new enquiry (app or website) e-mails a high-priority lead alert to
     * config('services.leads.notify_email'). Sent right after the HTTP response,
     * so the user does not wait for SMTP and a mail failure never loses the lead.
     */
    protected static function booted()
    {
        static::created(function (BusinessEnquiry $enquiry) {
            $to = array_values(array_filter(array_map('trim', explode(',', (string) config('services.leads.notify_email')))));
            if (!$to) {
                return;
            }

            $id = $enquiry->id;
            dispatch(function () use ($id, $to) {
                $lead = BusinessEnquiry::find($id);
                if (!$lead) {
                    return;
                }
                try {
                    \Illuminate\Support\Facades\Mail::to($to)->send(new \App\Mail\NewLeadMail($lead));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Lead alert e-mail failed', ['enquiry_id' => $id, 'error' => $e->getMessage()]);
                }
            })->afterResponse();
        });
    }
}
