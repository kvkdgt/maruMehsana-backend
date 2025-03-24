<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessEnquiry extends Model
{
    use HasFactory;
    protected $fillable = ['business_name', 'owner_name', 'mobile_no', 'whatsapp_no', 'status'];

    protected $attributes = [
        'status' => 'Pending',
    ];
}
