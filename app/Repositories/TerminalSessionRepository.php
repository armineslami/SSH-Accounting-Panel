<?php

namespace App\Repositories;

use App\Interfaces\TerminalSessionRepositoryInterface;
use App\Models\TerminalSession;

class TerminalSessionRepository implements TerminalSessionRepositoryInterface
{
    // Define your repository methods here
    public static function create(string $token, string $followUpToken = null, string $command, string $request = null): TerminalSession
    {
        return TerminalSession::create([
            "token" => $token,
            "follow_up_token" => $followUpToken,
            "command" => $command,
            "request" => $request
        ]);
    }

    public static function find(string $token): ?TerminalSession
    {
        return TerminalSession::where('token', $token)->get()->first();
    }

    public static function deleteById(int $id): Int
    {
        return TerminalSession::destroy($id);
    }
}
