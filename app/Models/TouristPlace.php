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
        'latitude',
        'longitude',
        'visitors',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function placeImages()
    {
        return $this->hasMany(TouristPlaceImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(TouristPlaceReview::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Get formatted coordinates as a string
    public function getCoordinatesAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return $this->latitude . ', ' . $this->longitude;
        }
        return null;
    }

    // Check if location coordinates are available
    public function hasCoordinates()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
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