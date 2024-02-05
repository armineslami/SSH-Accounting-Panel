<?php

namespace App\Interfaces;

use App\Models\TerminalSession;

interface TerminalSessionRepositoryInterface
{
    public static function create(
        string $token, string $followUpToken = null, string $command, string $request = null
    ): TerminalSession;
    public static function find(string $token): ?TerminalSession;
    public static function deleteById(int $id): Int;
}
