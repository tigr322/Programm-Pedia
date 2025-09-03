<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
    protected $fillable = [
        'problem_id',
        'slug',
        'title',
        'summary',
        'environment',
        'root_cause',
        'steps',
        'verification',
        'pitfalls',
        'links',
        'score',

        // добавляем новые
        'pdf_path',
        'content',
    ];

    protected $casts = [
        'environment' => 'array',
        'links'       => 'array',
    ];

    public function problem()
    {
        return $this->belongsTo(Problem::class);
    }

    public function tags()
    {
        return $this->belongsToMany(TechTag::class, 'solution_tag')
                    ->using(Solution_Tag::class); // кастомная модель пивота
    }

    public function snippets()
    {
        return $this->hasMany(Snippet::class);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_solution');
    }
}
