<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobReport extends Model
{
    use HasFactory;

    protected $table = 'job_reports';

    protected $fillable = [
        'job_vacancy_id',
        'reported_by',
        'reason',
        'description',
        'status',
    ];

    public function job()
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }

    public function reporter()
    {
        return $this->belongsTo(AppUser::class, 'reported_by');
    }
}
