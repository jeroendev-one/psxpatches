<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_id',
        'name',
        'current_version',
        'content_id',
        'region',
        'icon',
        'publisher',
        'background',
        'latest_patch_size'
    ];

    // Relation to patches
    public function patches()
    {
        return $this->hasMany(Patch::class);
    }
}