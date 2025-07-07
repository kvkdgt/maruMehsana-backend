<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class NewsAgency extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 
        'email', 
        'username', 
        'logo', 
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function admin()
    {
        return $this->hasOne(AgencyAdmin::class, 'agency_id');
    }

    // You can add more relationships here like news articles, etc.
    // public function articles()
    // {
    //     return $this->hasMany(Article::class);
    // }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return null;
    }

    public function getInitialAttribute()
    {
        return strtoupper(substr($this->name, 0, 1));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Boot method to handle cascading operations
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($agency) {
            // Delete associated admin
            if ($agency->admin) {
                $agency->admin->delete();
            }
            
            // Delete logo file
            if ($agency->logo && Storage::exists('public/' . $agency->logo)) {
                Storage::delete('public/' . $agency->logo);
            }
        });
    }
}