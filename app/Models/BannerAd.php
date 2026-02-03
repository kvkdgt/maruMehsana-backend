<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerAd extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'image', 'link', 'status', 'touch', 'business_id', 'tourist_place_id'];

    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }
}
