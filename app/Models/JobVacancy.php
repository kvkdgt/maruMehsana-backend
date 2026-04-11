<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;

    protected $table = 'job_vacancies';

    protected $fillable = [
        'posted_by',
        'title',
        'company_name',
        'job_type',
        'location',
        'salary_min',
        'salary_max',
        'salary_type',
        'description',
        'requirements',
        'vacancies_count',
        'experience_required',
        'education_required',
        'gender_preference',
        'contact_name',
        'contact_mobile',
        'contact_email',
        'apply_via',
        'thumbnail',
        'expires_at',
        'status',
        'is_active',
        'views_count',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'salary_min' => 'integer',
        'salary_max' => 'integer',
        'vacancies_count' => 'integer',
        'views_count' => 'integer',
        'is_active' => 'integer',
    ];

    // Relationships
    public function poster()
    {
        return $this->belongsTo(AppUser::class, 'posted_by');
    }

    public function reports()
    {
        return $this->hasMany(JobReport::class, 'job_vacancy_id');
    }

    public function saves()
    {
        return $this->hasMany(JobSave::class, 'job_vacancy_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1)
                     ->whereIn('status', ['open', 'filled'])
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>=', now()->toDateString());
                     });
    }
}
