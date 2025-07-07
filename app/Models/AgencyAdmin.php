<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyAdmin extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'agency_id', 
        'name', 
        'email', 
        'username', 
        'password', 
        'phone', 
        'status'
    ];

    protected $hidden = [
        'password', 
        'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function agency()
    {
        return $this->belongsTo(NewsAgency::class, 'agency_id');
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
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

    // Boot method to handle agency deletion
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::deleting(function ($admin) {
    //         // When admin is deleted, you might want to handle agency status
    //         if ($admin->agency) {
    //             $admin->agency->update(['status' => 'inactive']);
    //         }
    //     });
    // }
}