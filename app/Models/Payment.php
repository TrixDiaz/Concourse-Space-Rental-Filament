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
        'water_bill',
        'electricity_bill',
        'water_consumption',
        'electricity_consumption',
        'amount',
        'rent_bill',
        'payment_status',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_details' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class);
    }
}
