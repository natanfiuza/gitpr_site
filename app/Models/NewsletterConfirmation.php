<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsletterConfirmation extends Model
{
    protected $fillable = [
        'uuid',
        'email',
        'is_confirmed',
        'date_confirmed',
    ];

    protected function casts(): array
    {
        return [
            'is_confirmed' => 'boolean',
            'date_confirmed' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include records not older than 24 hours.
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subHours(24));
    }
}
