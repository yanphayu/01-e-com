<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    public const PLACEMENT_TOP = 'top';

    public const PLACEMENT_HOME = 'home';

    public const PLACEMENT_BOTH = 'both';

    protected $fillable = [
        'placement',
        'headline',
        'subtext',
        'button_label',
        'link_url',
        'image',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlacement(Builder $query, ?string $placement): Builder
    {
        if ($placement === null || $placement === '') {
            return $query;
        }

        return $query->whereIn('placement', [$placement, self::PLACEMENT_BOTH]);
    }
}
