<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSave extends Model
{
    use HasFactory;

    protected $table = 'job_saves';

    protected $fillable = [
        'job_vacancy_id',
        'app_user_id',
    ];

    public function job()
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }
}
