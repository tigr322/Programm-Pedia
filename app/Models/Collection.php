<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = ['slug','title','description'];

    public function solutions()
    {
        return $this->belongsToMany(Solution::class, 'collection_solution');
    }
}

