<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Solution extends Model
{
    use Searchable;
    public function searchableAs(): string { return 'solutions'; }
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
        'markdown',

        'language',
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
    public function toSearchableArray(): array
    {
        return [
            'id'         => $this->id,
            'problem_id' => $this->problem_id,
            'content'    => strip_tags($this->content),
            'has_pdf'    => (bool)$this->pdf_path,
           
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
