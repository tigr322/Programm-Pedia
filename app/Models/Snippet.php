<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Snippet extends Model
{
    protected $fillable = ['solution_id','language','title','body','meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function solution()
    {
        return $this->belongsTo(Solution::class);
    }
}
