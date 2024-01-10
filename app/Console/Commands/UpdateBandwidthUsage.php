<?php

namespace App\Console\Commands;

use App\Models\Inbound;
use App\Repositories\ServerRepository;
use App\Utils\Utils;
use Illuminate\Console\Command;

class UpdateBandwidthUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-bandwidth-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates each user bandwidth usage';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Get the list of servers
        $servers = ServerRepository::all();

        if ($servers->isEmpty()) {
            return;
        }

        // Connect to each server using SSH and run the Bandwidth.sh script
        $response = [];

        foreach ($servers as $server) {
            $result = Utils::executeShellCommand(
                "app/Scripts/Main.sh -action Bandwidth -server_ip " . $server->address . " -server_port " . $server->port . " -server_username " . $server->username
            );

            $json = json_decode($result, true);

            if ($result && isset($json['code']) && $json['code'] === '1') {
                $response[$server->name]['code'] = $json['code'];
                $response[$server->name]['message'] = $json['message'];
                $response[$server->name]['users'] = $json['users'];
            }
        }

        $collection = collect($response);

        $collection->each(function ($server, $serverName) {
            collect($server['users'])->each(function ($data, $username) {
                $inbound = Inbound::where("username", $username)->first();

                /**
                 * If an inbound is found and if it's traffic limit is not null which means unlimited,
                 * then update its traffic limit. Also deactivate the inbound if remaining traffic is <= 0.
                 */
                if ($inbound && isset($inbound->traffic_limit)) {
                    // Calculate the bandwidth usage in GB
                    $bandwidth = round(($data['download'] + $data['upload']) / 1024, 2);

                    // Update the remaining traffic limit of the inbound
                    $remainingTraffic = $inbound->traffic_limit - $bandwidth;
                    $inbound->remaining_traffic = $remainingTraffic > 0 ? $remainingTraffic : 0;
                    $inbound->is_active = $inbound->remaining_traffic > 0 ? '1' : '0';
                    $inbound->save();

//                    echo "Bandwidth usage is " . $bandwidth . " GB for user '" . $inbound->username
//                        . "' and remaining is: " . $inbound->remaining_traffic . " GB.";

                    // Deactivate the inbound if the remaining traffic is <= 0
                    if ($inbound->remaining_traffic <= 0) {
                        $inbound = Utils::convertExpireAtDateToActiveDays($inbound);
                        $server = ServerRepository::byAddress($inbound->server_ip);
                        Utils::executeShellCommand(
                            "app/Scripts/Main.sh -action UpdateUser -username " . $inbound->username .
                            " -password " . $inbound->password . " -is_active " . $inbound->is_active . " -max_login "
                            . $inbound->max_login . ($inbound->active_days ? " -active_days " . $inbound->active_days : '') .
                            ($inbound->traffic_limit ? " -traffic_limit " . $inbound->traffic_limit : '')
                            . " -server_ip " . $server->address . " -server_port " . $server->port . " -server_username " . $server->username
                        );
                    }
                }
            });
        });
    }
}
