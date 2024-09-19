<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Space extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'concourse_id',
        'name',
        'price',
        'status',
        'is_active',
        'space_width',
        'space_length',
        'space_area',
        'space_dimension',
        'space_coordinates_x',
        'space_coordinates_y',
        'space_coordinates_x2',
        'space_coordinates_y2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function concourse()
    {
        return $this->belongsTo(Concourse::class, 'concourse_id');
    }
}
