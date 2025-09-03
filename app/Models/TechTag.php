<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechTag extends Model
{
    protected $table = 'tech_tags';

    protected $fillable = ['slug','name'];

    public function solutions()
    {
        return $this->belongsToMany(Solution::class, 'solution_tag');
    }
}
