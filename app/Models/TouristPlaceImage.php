<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TouristPlaceImage extends Model
{
    use HasFactory;

    protected $table = 'tourist_place_images';

    protected $fillable = [
        'image',
        'tourist_place_id',
    ];

    protected static function booted()
    {
        static::deleting(function ($placeImage) {
            // Check if the image exists and delete it from storage
            if ($placeImage->image && Storage::exists('public/' . $placeImage->image)) {
                Storage::delete('public/' . $placeImage->image); // Delete the image from the folder
            }
        });
    }

    public function deleteImage()
    {
        if ($this->image && Storage::exists('public/' . $this->image)) {
            Storage::delete('public/' . $this->image); // Delete the file
        }

        return $this->delete(); // Delete the database record
    }

    public function touristPlace()
    {
        return $this->belongsTo(TouristPlace::class, 'tourist_place_id');
    }
}
