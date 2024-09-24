<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'name',
        'status',
        'file_path',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
