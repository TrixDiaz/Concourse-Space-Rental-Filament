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
        'application_id',
        'concourse_id',
        'name',
        'email',
        'space_type',
        'price',
        'sqm',
        'status',
        'lease_start',
        'lease_end',
        'lease_due',
        'lease_term',
        'lease_status',
        'payment_status',
        'application_status',
        'requirements_status',
        'remarks',
        'water_bills',
        'electricity_bills',
        'rent_bills',
        'payment_due',
        'payment_due_date',
        'payment_due_status',
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

    protected $casts = [
        'sqm' => 'float',
        'price' => 'float',
        'lease_start' => 'datetime',
        'lease_end' => 'datetime',
        'lease_due' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function concourse()
    {
        return $this->belongsTo(Concourse::class, 'concourse_id');
    }

    public function updatePriceBasedOnRate()
    {
        $concourse = $this->concourse;
        if ($concourse && $concourse->rate) {
            $newPrice = $concourse->rate->price * $this->sqm;
            if ($this->price != $newPrice) {
                $this->update(['price' => $newPrice]);
            }
        }
    }
}
