<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\NewsAgency;

class NewsArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'is_active',
        'is_featured',
        'is_for_mehsana',
        'visitor',
        'agency_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_for_mehsana' => 'boolean',
    ];

    // Automatically generate slug when title is set
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    /**
     * The slug column is UNIQUE. Gujarati titles slugify to "" (or to the same
     * short ASCII bits, e.g. "2026"), so two such articles collided and the
     * second insert failed with a 500. Make every slug non-empty and unique.
     */
    protected static function booted()
    {
        static::saving(function (NewsArticle $article) {
            if (!$article->isDirty(['slug', 'title']) && $article->slug !== '' && $article->slug !== null) {
                return;
            }

            $base = $article->slug ?: 'news';
            $slug = $base;

            while (static::where('slug', $slug)
                ->when($article->exists, fn ($q) => $q->where('id', '!=', $article->id))
                ->exists()) {
                $slug = $base . '-' . Str::lower(Str::random(6));
            }

            $article->slug = $slug;
        });
    }

    /** Website URL of this article: /news/{id}/{slug} (slug falls back to "news-{id}"). */
    public function getWebUrlAttribute(): string
    {
        return route('news.show', ['id' => $this->id, 'slug' => $this->slug ?: 'news-' . $this->id]);
    }

    // Relationship with Agency
    public function agency()
    {
        return $this->belongsTo(NewsAgency::class);
    }

    // Accessor for image URL
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/news/' . $this->image) : null;
    }

    // Scope for active articles
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for featured articles
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePopular($query)
{
    return $query->orderBy('visitor', 'desc');
}

// Method to increment visitor count
public function incrementVisitor()
{
    $this->increment('visitor');
}
    // Scope for search
    public function scopeSearch($query, $search)
    {
        return $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
    }

    // Get excerpt with word limit
    public function getExcerptAttribute($value)
    {
        return Str::limit($value, 150);
    }
}