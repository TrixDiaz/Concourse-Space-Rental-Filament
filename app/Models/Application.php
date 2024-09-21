<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'concourse_id',
        'space_id',
        'business_name',
        'owner_name',
        'email',
        'phone_number',
        'address',
        'business_type',
        'requirements',
        'expiration_date',
        'remarks',
        'status',
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
