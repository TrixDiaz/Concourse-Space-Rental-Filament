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
        'application_status',
        'requirements_status',
        'remarks',
        'water_bills',
        'water_consumption',
        'electricity_bills',
        'electricity_consumption',
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
        'water_consumption',
        'electricity_consumption',
        'water_payment_status',
        'electricity_payment_status',
        'rent_payment_status',
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

    
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($space) {
            if ($space->wasChanged('water_consumption')) {
                $space->calculateWaterBill();
            }
        });
    }


    public static function updateWaterBillsForOccupiedSpaces()
    {
        $occupiedSpaces = self::where('status', 'occupied')->get();
        $allConsumptionSupplied = $occupiedSpaces->every(function ($space) {
            return $space->water_consumption !== null && $space->water_consumption > 0;
        });

        if ($allConsumptionSupplied) {
            foreach ($occupiedSpaces as $space) {
                $space->calculateWaterBill();
            }
            return true;
        }

        return false;
    }

    public function calculateWaterBill()
    {
        $concourse = $this->concourse;
        $totalMonthlyWater = $concourse->total_monthly_water;
        $totalWaterConsumption = $concourse->spaces()
            ->where('status', 'occupied')
            ->sum('water_consumption');

        if ($totalWaterConsumption <= 0) {
            $this->update(['water_bills' => 0]);
            return;
        }

        $waterRate = $totalMonthlyWater / $totalWaterConsumption;
        $waterBill = $waterRate * $this->water_consumption;

        $this->update(['water_bills' => round($waterBill, 2)]);
    }
}
