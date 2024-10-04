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
        'image',
        'layout',
        'lease_term',
        'is_active',
    ];

    public function getLocationAttribute()
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'formatted_address' => $this->address,
        ];
    }

    public function getFormattedState(): string
    {
        $state = $this->getState();
 
        if ($this->getIsLocation()) {
            return $state['formatted_address'];
        }
 
        return $state = $this->address;
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
