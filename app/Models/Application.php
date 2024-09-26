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
        'space_id',
        'concourse_id',
        'business_name',
        'owner_name',
        'email',
        'phone_number',
        'address',
        'status',
        'business_type',
        'concourse_lease_term',
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

    public function requirements()
    {
        return $this->hasMany(AppRequirement::class);
    }

    public function appRequirements()
{
        return $this->hasMany(AppRequirement::class);
    }
}