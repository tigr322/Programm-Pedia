<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Synonym extends Model
{

    public $timestamps = true;
    protected $table = 'synonyms';

    protected $fillable = ['term','alias'];

}
