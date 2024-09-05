<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateServerRequest;
use App\Http\Requests\UpdateServerRequest;
use App\Repositories\ServerRepository;
use App\Repositories\SettingRepository;
use App\Repositories\TerminalSessionRepository;
use App\Services\Terminal\Command\Command;
use App\Utils\Utils;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ServerController extends BaseController
{
    public function __invoke($id = null): View|RedirectResponse
    {
        if ($id) {
            $server = ServerRepository::byId($id);

            if (!$server) {
                return Redirect::route('servers.index');
            }

            return view('server.update', ['server' => $server]);
        }

        $servers = ServerRepository::paginate(SettingRepository::first()->app_paginate_number);
        return view('server.index', ['servers' => $servers]);
    }

    public function create(): View
    {
        return view('server.create');
    }

    public function store(CreateServerRequest $request): RedirectResponse
    {
        $validatedRequest   = $request->validated();
        $req["server"]      = $validatedRequest;

        $setUpSession = TerminalSessionRepository::create(
            token: Utils::generateRandomString(),
            followUpToken: null,
            command: Command::SET_UP_SERVER,
            request: json_encode($req, true)
        );

        $terminalSession = TerminalSessionRepository::create(
            token: Utils::generateRandomString(),
            followUpToken: $setUpSession->token,
            command: Command::TRANSFER_KEY,
            request: json_encode($req, true)
        );

        return Redirect::route('servers.create')->withInput()->with([
            'status', 'terminal-session-created',
            'terminal_session_token' => $terminalSession->token
        ]);
    }

    public function update(int $id, UpdateServerRequest $request): RedirectResponse
    {
        ServerRepository::update($id, $request->validated());
        return Redirect::route('servers.update', $id)->with('status', 'server-updated');
    }

    public function destroy(int $id, Request $request): RedirectResponse
    {
        $server = ServerRepository::byId($id);

        if (isset($request->force_delete)) {
            ServerRepository::deleteById($id);
            return Redirect::route('servers.index')->with("status", "server-deleted");
        }

        $req["server"]  = $server;
        $req["id"]      = $id;

        $terminalSession = TerminalSessionRepository::create(
            token: Utils::generateRandomString(),
            followUpToken: null,
            command: Command::DELETE_SERVER,
            request: json_encode($req, true)
        );

        return Redirect::route('servers.update', $id)->withInput()->with([
            'status', 'terminal-session-created',
            'terminal_session_token' => $terminalSession->token
        ]);
    }
}
