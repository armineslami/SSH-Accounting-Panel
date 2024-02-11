<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInboundRequest;
use App\Http\Requests\UpdateInboundRequest;
use App\Repositories\InboundRepository;
use App\Repositories\ServerRepository;
use App\Repositories\SettingRepository;
use App\Repositories\TerminalSessionRepository;
use App\Services\Terminal\Command\Command;
use App\Utils\Utils;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class InboundController extends BaseController
{
    public function __invoke($id = null): View|RedirectResponse
    {
        if ($id) {
            $inbound = InboundRepository::byId($id);

            if (!$inbound) {
//                abort(404);
                return Redirect::route('inbounds.index');
            }

            $servers = ServerRepository::all();

            return view(
                'inbound.update', ['inbound' =>
                    Utils::convertExpireAtDateToActiveDays($inbound), 'servers' => $servers
                ]
            );
        }

        $inbounds = InboundRepository::paginate(20);

        return view('inbound.index', ['inbounds' => $inbounds]);
    }

    public function create(): View
    {
        $settings = SettingRepository::first();
        $servers = ServerRepository::all();
        return view('inbound.create', ['settings' => $settings, 'servers' => $servers]);
    }

    public function store(CreateInboundRequest $request): RedirectResponse
    {
        $validatedRequest   = $request->validated();
        $server             = ServerRepository::byAddress($request->server_ip);
        $req                = array_merge(["inbound" => $validatedRequest], ["server" => $server]);

        $terminalSession = TerminalSessionRepository::create(
            token: Utils::generateRandomString(),
            followUpToken: null,
            command: Command::CREATE_INBOUND,
            request: json_encode($req, true)
        );

        return Redirect::route('inbounds.create')->withInput()->with([
            'status', 'terminal-session-created',
            'terminal_session_token' => $terminalSession->token
        ]);
    }

    public function update(int $id, UpdateInboundRequest $request): RedirectResponse
    {
        $validatedRequest           = $request->validated();
        $inbound                    = InboundRepository::byId($id);
        $validatedRequest["server"] = $inbound->server;
        $server                     = ServerRepository::byAddress($request->server_ip);
        $req                        = array_merge(["inbound" => $validatedRequest], ["server" => $server]);
        $req["id"]                  = $id;

        /**
         * If the server ip is changed for the inbound, we should create this user on the new server
         * and delete it from the old server. But if the server ip is not changed, just update the user on
         * the old server.
         */
        if ($request->delete_from_old_server === '1') {
            $updateSession = TerminalSessionRepository::create(
                token: Utils::generateRandomString(),
                followUpToken: null,
                command: Command::UPDATE_INBOUND,
                request: json_encode($req, true)
            );

            $terminalSession = TerminalSessionRepository::create(
                token: Utils::generateRandomString(),
                followUpToken: $updateSession->token,
                command: Command::DELETE_INBOUND,
                request: json_encode($req, true)
            );
        }
        else {
            $terminalSession = TerminalSessionRepository::create(
                token: Utils::generateRandomString(),
                followUpToken: null,
                command: Command::UPDATE_INBOUND,
                request: json_encode($req, true)
            );
        }


        return Redirect::route('inbounds.update', $id)->withInput()->with([
            'status', 'terminal-session-created',
            'terminal_session_token' => $terminalSession->token
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $inbound = InboundRepository::byId($id);

        if (is_null($inbound->server)) {
            // Only delete the inbound from the database
            InboundRepository::deleteById($id);
            return Redirect::route('inbounds.index')->with("status", "inbound-deleted");
        }

        $req        = array_merge(["inbound" => $inbound], ["server" => $inbound->server]);
        $req["id"]  = $id;

        $terminalSession = TerminalSessionRepository::create(
            token: Utils::generateRandomString(),
            followUpToken: null,
            command: Command::DELETE_INBOUND,
            request: json_encode($req, true)
        );

        return Redirect::route('inbounds.update', $id)->withInput()->with([
            'status', 'terminal-session-created',
            'terminal_session_token' => $terminalSession->token
        ]);
    }

    public function search(Request $request): View
    {
        $result = InboundRepository::search(query: $request->input('query'));
        return view('inbound.index', ['search_result' => $result, 'query' => $request->input('query')]);
    }
}
