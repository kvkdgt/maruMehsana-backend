<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TouristPlaceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'tourist_place_id',
        'app_user_id',
        'rating',
        'comment'
    ];

    public function touristPlace()
    {
        return $this->belongsTo(TouristPlace::class);
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }
}
