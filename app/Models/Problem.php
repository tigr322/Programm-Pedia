<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Problem extends Model
{
    use Searchable;
    protected $fillable =[
        'user_id',
        'slug'          ,
        'title'         ,
        'description'    ,
        'metadata',
        'personaly',
    ];

    public function searchableAs(): string { return 'problems'; }
    public function getRouteKeyName(): string { return 'slug'; }

    // связь на решения (если ещё нет)
    public function solutions()
    {
        return $this->hasMany(Solution::class);
    }
    public function user()
    {
        return $this->BelongTo(User::class);
    }
    protected $casts = [
        'metadata' => 'array',
        'personaly' => 'boolean',
    ];
    public function toSearchableArray(): array
    {
        // безопасно, без tags
        $solutionsCount = method_exists($this, 'solutions')
            ? (int) $this->solutions()->count()
            : 0;

        return [
            'id'             => $this->id,
            'slug'           => $this->slug,
            'title'          => $this->title,
            'description'    => strip_tags((string) $this->description),
            'user_id'        => (int) $this->user_id,
            'status'         => (string) $this->status,
            'votes'          => (int) ($this->votes ?? 0),
            'solutions_count'=> $solutionsCount,
            'created_at'     => optional($this->created_at)->toISOString(),
        ];
    }
}
