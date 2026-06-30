<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    use HasFactory;

    protected $table = 'search_histories';

    protected $fillable = [
        'app_user_id',
        'query',
    ];

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }
}
