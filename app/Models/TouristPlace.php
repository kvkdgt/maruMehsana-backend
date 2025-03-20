<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TouristPlace extends Model
{
    use HasFactory;

    protected $table = 'tourist_places';

    protected $fillable = [
        'name',
        'description',
        'thumbnail',
        'location',
        'visitors',
        'created_by',
        'updated_by',
    ];

    public function placeImages()
    {
        return $this->hasMany(TouristPlaceImage::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Ensure related place images are deleted when a tourist place is deleted
    public static function booted()
    {
        static::deleting(function ($place) {
            $place->placeImages->each(function ($image) {
                $image->delete();
            });
        });
    }
}
