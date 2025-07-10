<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsCategory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'news_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'slug',
        'color',
        'status',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug when creating
        static::creating(function ($newsCategory) {
            if (empty($newsCategory->slug)) {
                $newsCategory->slug = Str::slug($newsCategory->name);
                
                // Ensure unique slug
                $originalSlug = $newsCategory->slug;
                $count = 1;
                while (static::where('slug', $newsCategory->slug)->exists()) {
                    $newsCategory->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        // Auto-generate slug when updating
        static::updating(function ($newsCategory) {
            if ($newsCategory->isDirty('name')) {
                $newsCategory->slug = Str::slug($newsCategory->name);
                
                // Ensure unique slug (excluding current record)
                $originalSlug = $newsCategory->slug;
                $count = 1;
                while (static::where('slug', $newsCategory->slug)->where('id', '!=', $newsCategory->id)->exists()) {
                    $newsCategory->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include inactive categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    /**
     * Scope a query to order by sort order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Get the status badge HTML.
     *
     * @return string
     */
    public function getStatusBadgeAttribute()
    {
        return $this->status 
            ? '<span class="status-badge active">Active</span>' 
            : '<span class="status-badge inactive">Inactive</span>';
    }

    /**
     * Get the color preview HTML.
     *
     * @return string
     */
    public function getColorPreviewAttribute()
    {
        return '<div class="color-preview" style="background-color: ' . $this->color . ';"></div>';
    }

    // Future relationship with news articles
    // public function articles()
    // {
    //     return $this->hasMany(NewsArticle::class, 'category_id');
    // }
}