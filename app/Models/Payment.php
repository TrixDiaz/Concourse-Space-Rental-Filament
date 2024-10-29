<?php

namespace App\Models;
    
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'payment_type',
        'payment_method',
        'concourse_id',
        'space_id',
        'water_bill',
        'water_due',
        'water_consumption',
        'electricity_bill',
        'electricity_due',
        'electricity_consumption',
        'amount',
        'rent_bill',
        'rent_due',
        'payment_status',
        'due_date',
        'paid_date',
        'penalty',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_details' => 'array',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    // Add these constants
    const STATUS_PAID = 'paid';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_DELAYED = 'delayed';

    public function updateDelayedStatus()
    {
        if ($this->due_date && $this->paid_date) {
            $this->days_delayed = max(0, $this->paid_date->diffInDays($this->due_date));
            if ($this->days_delayed > 0) {
                $this->payment_status = self::STATUS_DELAYED;
            }
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $this->days_delayed = $this->due_date->diffInDays(now());
            $this->payment_status = self::STATUS_DELAYED;
        }
        $this->save();
    }
}
