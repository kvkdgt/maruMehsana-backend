<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TouristPlace extends Model
{
    use HasFactory;

    protected $table = 'tourist_places';

    protected $fillable = [
        'name',
        'description',
        'thumbnail',
        'location',
        'latitude',
        'longitude',
        'visitors',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function placeImages()
    {
        return $this->hasMany(TouristPlaceImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(TouristPlaceReview::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Get formatted coordinates as a string
    public function getCoordinatesAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return $this->latitude . ', ' . $this->longitude;
        }
        return null;
    }

    // Check if location coordinates are available
    /**
     * Admins often paste a Google Maps link into "location" instead of an address.
     * True when the location field holds a URL rather than readable text.
     */
    public function locationIsUrl(): bool
    {
        return (bool) preg_match('~^https?://~i', trim((string) $this->location));
    }

    /** Readable location text for the website; null when the field is only a link. */
    public function getLocationTextAttribute(): ?string
    {
        $text = trim((string) $this->location);
        return $text === '' || $this->locationIsUrl() ? null : $text;
    }

    /** The pasted maps link, if the location field holds one. */
    public function getMapsLinkAttribute(): ?string
    {
        return $this->locationIsUrl() ? trim((string) $this->location) : null;
    }

    /**
     * [lat, lng] from the latitude/longitude columns, or else parsed from a pasted
     * Google Maps link ("!3dLAT!4dLNG" is the place pin, "@LAT,LNG" the map centre).
     */
    public function geoPoint(): ?array
    {
        if ($this->hasCoordinates()) {
            return [(float) $this->latitude, (float) $this->longitude];
        }

        $url = urldecode((string) $this->maps_link);
        if ($url !== ''
            && (preg_match('/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/', $url, $m)
                || preg_match('/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', $url, $m))) {
            $lat = (float) $m[1];
            $lng = (float) $m[2];
            if (abs($lat) <= 90 && abs($lng) <= 180) {
                return [$lat, $lng];
            }
        }

        return null;
    }

    public function hasCoordinates()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    // Ensure related place images are deleted when a tourist place is deleted
    public static function booted()
    {
        static::deleting(function ($place) {
            $place->placeImages->each(function ($image) {
                $image->delete();
            });
        });
    }
}