<?php

namespace App\Http\Controllers;

use App\Http\Requests\TerminalSessionRequest;
use App\Models\TerminalSession;
use App\Repositories\TerminalSessionRepository;
use App\Services\Terminal\TerminalService;

class TerminalController extends Controller
{
    public function __invoke(TerminalSessionRequest $request)
    {
        $session = TerminalSessionRepository::find($request->token);

        if (is_null($session)) {
            return response()->json([
                "code" => "0", "message" => "Invalid session token"
            ], 422);
        }

        $service = new TerminalService();
        $service::setup();

        self::run($service, $session);

        if ($service::failed()) {
            return response()->json([
                "code" => "0", "message" => "Failed to run the task"
            ], 422);
        }

        exit;
    }

    private static function run(TerminalService $service, TerminalSession $terminalSession): void
    {
        $service::run($terminalSession);

        $followUpSession = $terminalSession->follow_up_token ? TerminalSessionRepository::find($terminalSession->follow_up_token) : null;

        if (!is_null($followUpSession)) {
            self::run($service, $followUpSession);
        }
    }
}
