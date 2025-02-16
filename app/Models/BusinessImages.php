<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BusinessImages extends Model
{
    use HasFactory;
    protected $table = 'business_images';
    protected $fillable = [
        'image',
        'business_id'
    ];

    protected static function booted()
    {
        static::deleting(function ($businessImage) {
            // Check if the image exists and delete it from storage
            if ($businessImage->image && Storage::exists('public/' . $businessImage->image)) {
                Storage::delete('public/' . $businessImage->image); // Delete the image from the folder
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
}
