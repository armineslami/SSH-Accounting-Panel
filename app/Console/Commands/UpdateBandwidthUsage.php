<?php

namespace App\Console\Commands;

use App\Models\Inbound;
use App\Models\Server;
use App\Repositories\InboundRepository;
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

        $response = [];

        // Connect to each server using SSH and run the Bandwidth.sh script
        foreach ($servers as $server) {
            $result = self::bandwidth($server);

            if (!$result) {
                continue;
            }

            $json = json_decode($result, true);

            if (isset($json['code']) && $json['code'] === '1') {
                $response[$server->name]['code'] = $json['code'];
                $response[$server->name]['message'] = $json['message'];
                $response[$server->name]['users'] = $json['users'];
            }
        }

        $collection = collect($response);

        $collection->each(function ($server, $serverName) {
            collect($server['users'])->each(function ($data, $username) {
//                $inbound = Inbound::where("username", $username)->first();
                $inbound = InboundRepository::byUsername($username);

                /**
                 * If an inbound is found and if it's traffic limit is not null which means unlimited,
                 * then update its traffic limit. Also deactivate the inbound if remaining traffic is <= 0.
                 */
                if ($inbound && isset($inbound->traffic_limit)) {
                    // Calculate the bandwidth usage in GB
                    $bandwidth = round(($data['download'] + $data['upload']) / 1024, 2);

                    // Update the remaining traffic limit of the inbound
                    $remainingTraffic = $inbound->remaining_traffic - $bandwidth;
                    $inbound->remaining_traffic = $remainingTraffic > 0 ? $remainingTraffic : 0;
                    $inbound->is_active = $inbound->remaining_traffic > 0 ? '1' : '0';
                    $inbound->save();

//                    echo "Bandwidth usage is " . $bandwidth . " GB for user '" . $inbound->username
//                        . "' and remaining is: " . $inbound->remaining_traffic . " GB.";

                    // Deactivate the inbound if the remaining traffic is <= 0
                    if ($inbound->remaining_traffic <= 0 && !is_null($inbound->server)) {
                        $inbound = Utils::convertExpireAtDateToActiveDays($inbound);
                        self::updateInbound($inbound, 0);
                    }
                }
            });
        });
    }

    private static function bandwidth(Server $server): string|false|null
    {
        $script     = self::script(\App\Services\Terminal\Command\Command::BANDWIDTH);
        $key        = self::key();
        $ip         = self::ip();

        if (is_null($ip)) {
            return null;
        }

        if ($ip == $server->address) {
            $result = shell_exec("bash -s < $script 2>&1");
        }
        else {
            $result = shell_exec("sudo ssh -i $key -p $server->port $server->username@$server->address 'bash -s' < $script 2>&1");
        }

        return $result;
    }

    private static function updateInbound(Inbound $inbound, int $retryCount): void
    {
        $script     = self::script(\App\Services\Terminal\Command\Command::UPDATE_INBOUND);
        $key        = self::key();
        $ip         = self::ip();

        if (is_null($ip)) {
            sleep(5);
            if ($retryCount < 3) {
                self::updateInbound($inbound, $retryCount+1);
            }
        }

        if ($ip == $inbound->server->address) {
            $command = "export USERNAME={$inbound->username}; export PASSWORD={$inbound->user_password}; export IS_ACTIVE={$inbound->is_active}; export MAX_LOGIN={$inbound->max_login}; export ACTIVE_DAYS={$inbound->active_days}; export TRAFFIC_LIMIT={$inbound->traffic_limit}; bash -s < {$script} 2>&1";
        }
        else {
            $command = "sudo ssh -o StrictHostKeyChecking=accept-new -i {$key} -p {$inbound->server->port} {$inbound->server->username}@{$inbound->server->address} 'export USERNAME={$inbound->username}; export PASSWORD={$inbound->password}; export IS_ACTIVE={$inbound->is_active}; export MAX_LOGIN={$inbound->max_login}; export ACTIVE_DAYS={$inbound->active_days}; export TRAFFIC_LIMIT={$inbound->traffic_limit}; bash -s' < {$script} 2>&1";
        }

        shell_exec($command);
    }

    private static function script(string $command): string
    {
        return base_path("app/Scripts/".$command.".sh");
    }

    private static function ip(): string|null|false
    {
        $ip = shell_exec("curl -s ipv4.icanhazip.com");
        if ($ip === null || $ip === false) {
            return $ip;
        }
        return trim($ip);
    }

    private static function key(): string
    {
        return base_path("storage/keys/ssh_accounting_panel");
    }
}
