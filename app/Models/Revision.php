<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $fillable = ['revisable_type','revisable_id','user_id','summary','diff'];

    protected $casts = [
        'diff' => 'array',
    ];

    public function revisable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
