<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Business extends Model
{
    use HasFactory;

    protected $table = 'businesses';

    protected $fillable = [
        'name',
        'description',
        'thumbnail',
        'category_id',
        'visitors',
        'mobile_no',
        'whatsapp_no',
        'website_url',
        'email_id',
        'services',  
        'products',
        'created_by',
        'updated_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function businessImages()
    {
        return $this->hasMany(BusinessImages::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Ensure related business images are deleted when a business is deleted
    public static function booted()
    {
        static::deleting(function ($business) {
            $business->businessImages->each(function ($image) {
                $image->delete();
            });
        });
    }
}
