<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'placement_key',
        'name',
        'is_active',
        'ad_unit_id_android'
    ];
}
