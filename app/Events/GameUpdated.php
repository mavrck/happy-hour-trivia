<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Throwable;

/**
 * Tells every open screen that the game changed so they refetch its state right away.
 * It carries no data on purpose: the state endpoint stays the single source of truth.
 */
class GameUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    public function broadcastOn(): array
    {
        return [new Channel('game')];
    }

    public function broadcastAs(): string
    {
        return 'game.updated';
    }

    /** Push a notification, but never let a WebSocket outage break the game. Polling still works. */
    public static function send(): void
    {
        try {
            static::dispatch();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
