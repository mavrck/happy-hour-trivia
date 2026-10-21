<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $guarded = [];

    protected $hidden = ['token', 'pin'];

    /**
     * Banned players stay in the table (so their name and device stay blocked) but are invisible to the
     * game: not counted, not on the leaderboard, not on the roster. Use withoutGlobalScopes() to see them.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active', fn (Builder $query) => $query->whereNull('banned_at'));
    }

    protected function casts(): array
    {
        return ['banned_at' => 'datetime'];
    }
}
