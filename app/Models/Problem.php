<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Один Problem может иметь много Solution
    public function solutions()
    {
        return $this->hasMany(Solution::class);
    }
}
