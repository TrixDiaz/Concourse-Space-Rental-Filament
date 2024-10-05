<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Concourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'rate_id',
        'lat',
        'lng',
        'spaces',
        'total_monthly_rent',
        'total_monthly_water',
        'total_monthly_electricity',
        'image',
        'layout',
        'lease_term',
        'is_active',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function getLocationAttribute()
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'formatted_address' => $this->address,
        ];
    }

    public function setLocationAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['lat'] = $value['lat'] ?? null;
            $this->attributes['lng'] = $value['lng'] ?? null;
            $this->attributes['address'] = $value['formatted_address'] ?? null;
        }
    }

    public function concourseRate()
    {
        return $this->belongsTo(ConcourseRate::class, 'rate_id')->where('is_active', true);
    }

    public function spaces()
    {
        return $this->hasMany(Space::class);
    }
}
