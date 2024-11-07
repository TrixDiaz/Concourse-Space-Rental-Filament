<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class RenewAppRequirements extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'requirement_id',
        'user_id',
        'space_id',
        'concourse_id',
        'application_id',
        'name',
        'status',
        'file',
    ];

    public function renewApplication()
    {
        return $this->belongsTo(Renew::class);
    }

    public function renewRequirement()
    {
        return $this->belongsTo(Requirement::class);
    }

}
