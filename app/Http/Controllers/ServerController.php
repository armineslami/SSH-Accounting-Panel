<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateServerRequest;
use App\Http\Requests\UpdateServerRequest;
use App\Repositories\ServerRepository;
use App\Utils\Utils;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ServerController extends BaseController
{
    public function __invoke($id = null): View
    {
        if ($id) {
            $server = ServerRepository::byId($id);
            return view('server.update', ['server' => $server]);
        }

        $servers = ServerRepository::paginate(20);
        return view('server.index', ['servers' => $servers]);
    }

    public function create(): View
    {
        return view('server.create');
    }

    public function store(CreateServerRequest $request): RedirectResponse
    {
        $request->validated();

        try {
            $result = Utils::executeShellCommand(
                "app/Scripts/Main.sh -action CopyPublicAuthKey -server_ip ".$request->address." -server_port ".$request->port." -server_username ".$request->username." -server_password ".$request->password
            );

            $redirect = $this->redirectIfFailed(to: 'servers.create', status: 'server-not-created', response: $result);

            if (!is_null($redirect))
                return $redirect;

            $result = Utils::executeShellCommand(
                "app/Scripts/Main.sh -action SetUpServer -server_ip ".$request->address." -server_port ".$request->port." -server_username ".$request->username. " -server_udp_port ".$request->udp_port
            );

            $redirect = $this->redirectIfFailed(to: 'servers.create', status: 'server-not-created', response: $result);

            if (!is_null($redirect))
                return $redirect;

        } catch (\ErrorException $error) {
            return Redirect::route('servers.create')->with('status', 'server-not-created')->withInput();
        }

        ServerRepository::create(
            $request->name,
            $request->address,
            $request->username,
            $request->port,
            $request->udp_port
        );

        return Redirect::route('servers.create')->with('status', 'server-created');
    }

    public function update(int $id, UpdateServerRequest $request): RedirectResponse
    {
        ServerRepository::update($id, $request->validated());
        return Redirect::route('servers.update', $id)->with('status', 'server-updated');
    }

    public function destroy(int $id): RedirectResponse
    {
        $server = ServerRepository::byId($id);

        $result = Utils::executeShellCommand(
            "app/Scripts/Main.sh -action RemoveServer -server_ip ".$server->address." -server_port ".$server->port." -server_username ".$server->username
        );

        $redirect = $this->redirectIfFailed(to: 'servers.update', status: 'servers.update', response: $result, id: $id);

        if (!is_null($redirect))
            return $redirect;

        ServerRepository::deleteById($id);

        return Redirect::route('servers.index')->with('status', 'server-deleted');
    }
}
