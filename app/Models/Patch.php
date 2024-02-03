<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patch extends Model
{
    protected $fillable = [
        'version',
        'icon',
        'size',
        'endpoint'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}