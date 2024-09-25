<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'user_id',
        'requirement_id',
        'status',
        'file',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }
}