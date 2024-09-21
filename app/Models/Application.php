<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'space_id',
        'concourse_id',
        'business_name',
        'owner_name',
        'email',
        'phone_number',
        'address',
        'business_type',
        'expiration_date',
        'requirements',
    ];

    protected $casts = [
        'requirements' => 'array',
        'expiration_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
    
    public function concourse()
    {
        return $this->belongsTo(Concourse::class);
    }
}
