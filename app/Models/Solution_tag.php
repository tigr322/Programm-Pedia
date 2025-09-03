<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Solution_Tag extends Pivot
{
    protected $table = 'solution_tag';

    public $timestamps = false; // если их нет

    public function solution()
    {
        return $this->belongsTo(Solution::class);
    }

    public function tag()
    {
        return $this->belongsTo(TechTag::class, 'tech_tag_id');
    }
}
