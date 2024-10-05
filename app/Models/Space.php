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
        'sqm',
        'status',
        'lease_start',
        'lease_end',
        'lease_due',
        'lease_term',
        'lease_status',
        'bills',
        'monthly_payment',
        'payment_status',
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
        'bills' => 'array',
        'sqm' => 'float',
        'price' => 'float',
        'monthly_payment' => 'float',
    ];

    public function getWaterBillAttribute()
    {
        return $this->bills['water'] ?? 0;
    }

    public function getElectricityBillAttribute()
    {
        return $this->bills['electricity'] ?? 0;
    }

    public function getAdditionalBillsAttribute()
    {
        return $this->bills['additional'] ?? [];
    }

    public function getTotalBillsAttribute()
    {
        $additionalTotal = collect($this->additional_bills)->sum('amount');
        return $this->water_bill + $this->electricity_bill + $additionalTotal;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
