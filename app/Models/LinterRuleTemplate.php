<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinterRuleTemplate extends Model
{
    protected $fillable = [
        'name',
        'extensions',
        'regex',
        'message',
        'ignore_comments',
        'ignore_paths',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'extensions' => 'array',
            'ignore_paths' => 'array',
            'ignore_comments' => 'boolean',
        ];
    }
}
