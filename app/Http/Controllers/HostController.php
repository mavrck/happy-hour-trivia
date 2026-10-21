<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $password = (string) $request->input('password');

        if (! hash_equals(config('game.host_password'), $password)) {
            return response()->json(['message' => 'Wrong password.'], 422);
        }

        $request->session()->put('host', true);

        return response()->json(['ok' => true]);
    }

    public function state(): JsonResponse
    {
        return response()->json(GameController::payload(null, forHost: true));
    }

    public function action(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:start,reveal,next,reset,wipe,seconds,change,count,late'],
            'allow_late' => ['required_if:action,late', 'boolean'],
            'count' => ['required_if:action,count', 'integer', 'min:5', 'max:40'],
            'allow' => ['required_if:action,change', 'boolean'],
            'seconds' => ['required_if:action,seconds', 'integer', 'min:5', 'max:30'],
        ]);
        $action = $data['action'];

        $game = Game::current()->load('question');

        match ($action) {
            'start' => $game->state === Game::LOBBY ? $game->start() : null,
            'reveal' => $game->reveal(),
            'next' => $game->state === Game::REVEAL ? $game->next() : null,
            'reset' => $game->reset(),
            'wipe' => $game->reset(wipePlayers: true),
            'change' => $game->state === Game::LOBBY ? $game->update(['allow_change' => (bool) $data['allow']]) : null,
            'late' => $game->update(['allow_late_join' => (bool) $data['allow_late']]),
            'count' => $game->state === Game::LOBBY ? $game->update(['question_count' => $data['count']]) : null,
            'seconds' => $game->state === Game::LOBBY ? $game->update(['question_seconds' => $data['seconds']]) : null,
        };

        return response()->json(GameController::payload(null, forHost: true));
    }
}
