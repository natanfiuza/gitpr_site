<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'github',
        'phone',
        'lang',
        'is_canceled',
        'date_canceled',
    ];

    protected function casts(): array
    {
        return [
            'is_canceled' => 'boolean',
            'date_canceled' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include active (not canceled) subscribers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_canceled', false);
    }
}
