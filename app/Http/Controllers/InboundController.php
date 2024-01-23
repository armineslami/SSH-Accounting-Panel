<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInboundRequest;
use App\Http\Requests\UpdateInboundRequest;
use App\Repositories\InboundRepository;
use App\Repositories\ServerRepository;
use App\Repositories\SettingRepository;
use App\Utils\Utils;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class InboundController extends BaseController
{
    public function __invoke($id = null): View
    {
        if ($id) {
            $inbound = InboundRepository::byId($id);
            $servers = ServerRepository::all();

            return view(
                'inbound.update', ['inbound' =>
                    !$inbound ?:
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
        $request->validated();

        $server = ServerRepository::byAddress($request->server_ip);

        try {
            $result = Utils::executeShellCommand(
                "app/Scripts/Main.sh -action CreateUser -username ".$request->username." -password ".$request->user_password." -is_active ".$request->is_active." -max_login ".$request->max_login. ($request->active_days ? " -active_days ".$request->active_days : ''). ($request->traffic_limit ? " -traffic_limit ".$request->traffic_limit : '')." -server_ip ".$server->address." -server_port ".$server->port." -server_username ".$server->username
            );

            $redirect = $this->redirectIfFailed(to: 'inbounds.create', status: 'inbound-not-created', response: $result);

            if (!is_null($redirect))
                return $redirect;
        } catch (\ErrorException $error) {
            return Redirect::route('inbounds.create')->with('status', 'inbound-not-created')->withInput();
        }

        InboundRepository::create(
            username: $request->username,
            password: $request->user_password,
            is_active: $request->is_active,
            traffic_limit: $request->traffic_limit ?? null,
            remaining_traffic: $request->traffic_limit ?? null,
            max_login: $request->max_login,
            server_ip: $request->server_ip,
            expires_at: $request->active_days ? Carbon::now()->addDays($request->active_days) : null
        );

        return Redirect::route('inbounds.create')->with('status', 'inbound-created');
    }

    public function update(int $id, UpdateInboundRequest $request): RedirectResponse
    {
        $validated_inbound = $request->validated();
        $validated_inbound['expires_at'] = Utils::convertActiveDaysToExpireAtDate($request->active_days);
        if ($validated_inbound['traffic_limit'] < $validated_inbound['remaining_traffic']) {
            $validated_inbound['remaining_traffic'] = $validated_inbound['traffic_limit'];
        }
        else if (isset($validated_inbound['traffic_limit']) && !isset($validated_inbound['remaining_traffic'])) {
            $validated_inbound['remaining_traffic'] = $validated_inbound['traffic_limit'];
        }
        else if (isset($validated_inbound['remaining_traffic']) && !isset($validated_inbound['traffic_limit'])) {
            $validated_inbound['remaining_traffic'] = $validated_inbound['traffic_limit'];
        }

        $inbound    = InboundRepository::byId($id);
        $server     = ServerRepository::byAddress($request->server_ip);
        $action     = $inbound && $inbound->server_ip != $request->server_ip ? "CreateUser" : "UpdateUser";

        try {
            /**
             * If the server ip is changed for the inbound, we should create this user on the new server
             * and delete it from the old server. But if the server ip is not changed, just update the user on
             * the old server.
             */
            if ($request->delete_from_old_server === '1') {
                $action = "CreateUser";
                $result = Utils::executeShellCommand(
                    "app/Scripts/Main.sh -action DeleteUser -username ".$inbound->username." -server_ip ".$inbound->server->address." -server_port ".$inbound->server->port." -server_username ".$inbound->server->username
                );

                $redirect = $this->redirectIfFailed(to: 'inbounds.update', status: 'inbound-not-updated', response: $result, id: $id);

                if (!is_null($redirect))
                    return $redirect;
            }

            $result = Utils::executeShellCommand(
                "app/Scripts/Main.sh -action ".$action." -username ".$request->username." -password ".$request->user_password." -is_active ".$request->is_active." -max_login ".$request->max_login. ($request->active_days ? " -active_days ".$request->active_days : ''). ($request->traffic_limit ? " -traffic_limit ".$request->traffic_limit : '')." -server_ip ".$server->address." -server_port ".$server->port." -server_username ".$server->username
            );

            $redirect = $this->redirectIfFailed(to: 'inbounds.update', status: 'inbound-not-updated', response: $result, id: $id);

            if (!is_null($redirect))
                return $redirect;

        } catch (\ErrorException $error) {
            return Redirect::route('inbounds.update', $id)->with('status', 'inbound-not-updated')->withInput();
        }

        InboundRepository::update(
            $id,
            $validated_inbound
        );
        return Redirect::route('inbounds.update', $id)->with('status', 'inbound-updated');
    }

    public function destroy(int $id): RedirectResponse
    {
        $inbound = InboundRepository::byId($id);

        try {
            $result = Utils::executeShellCommand(
                "app/Scripts/Main.sh -action DeleteUser -username ".$inbound->username." -server_ip ".$inbound->server->address." -server_port ".$inbound->server->port." -server_username ".$inbound->server->username
            );

            $redirect = $this->redirectIfFailed(to: 'inbounds.update', status: 'inbound-not-deleted', response: $result, id: $id);

            if (!is_null($redirect))
                return $redirect;

        } catch (\ErrorException $error) {
            return Redirect::route('inbounds.update', $id)->with('status', 'inbound-not-deleted')->withInput();
        }

        InboundRepository::deleteById($id);

        return Redirect::route('inbounds.index')->with('status', 'inbound-deleted');
    }

    public function search(Request $request): View
    {
        $result = InboundRepository::search(query: $request->input('query'));
        return view('inbound.index', ['search_result' => $result, 'query' => $request->input('query')]);
    }
}
