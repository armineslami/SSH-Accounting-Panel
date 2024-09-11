<?php

namespace App\Console\Commands;

use App\Models\Inbound;
use App\Models\Server;
use App\Repositories\InboundRepository;
use App\Repositories\OutlineRepository;
use App\Repositories\ServerRepository;
use App\Services\Outline\OutlineService;
use App\Utils\Utils;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

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

        self::calculateSshTraffic($servers);
        self::calculateOutlineTraffic($servers);
    }

    private static function calculateSshTraffic(Collection $servers): void
    {
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

                $inbound = InboundRepository::byUsername($username);

                if ($inbound) {
                    /**
                     * If an inbound is found and if it's traffic limit is not null, it means traffic is limited
                     * so update its traffic limit. Also deactivate the inbound if remaining traffic is <= 0.
                     */
                    if (isset($inbound->traffic_limit)) {
                        // Calculate SSH bandwidth usage in GB
                        $sshBandwidth = round(($data['download'] + $data['upload']) / 1024, 2);
                        $inbound = self::updateInboundRemainingTraffic($inbound, $sshBandwidth);
                    }

                    /**
                     * If remaining day is 0, deactivate the inbound on the database and no need
                     * to ssh to the server and update the expiry date because user is already expired.
                     */
                    if (isset($inbound->expires_at)) {
                        $inbound = self::checkInboundExpiry($inbound);
                    }

                    $inbound->save();

                    // Deactivate the inbound on the server if it's not active
                    if ($inbound->is_active === '0' && !is_null($inbound->server)) {
                        $inbound = Utils::convertExpireAtDateToActiveDays($inbound);
                        self::updateInbound($inbound, 0);
                    }
                }
            });
        });
    }

    private static function calculateOutlineTraffic(Collection $servers): void
    {
        foreach ($servers as $server) {
            $inbounds = $server->inbounds();
            $inbounds->each(function ($inbound, $index) {
                if (isset($inbound->outline) && isset($inbound->server->address)) {

                    $outline = OutlineRepository::byInboundId($inbound->id);

                    if (!is_null($outline)) {
                        /**
                         * Outline api only returns total used traffic and there is no api to set it to 0 when usage
                         * is calculated. For ssh bandwidth this is done by deleting log files.
                         * To make this happen for outline, the total usage is stored in the database and each time
                         * a new total usage is fetched using {@link OutlineService::getUsedTrafficForKeyInGB}. Then
                         * to calculate the new bandwidth usage, database last value is subtracted from the total usage.
                         */
                        $outlineTotalBandwidth = OutlineService::getUsedTrafficForKeyInGB($inbound->server->address, $outline->outline_id);
                        $outlineBandwidth = $outlineTotalBandwidth - $outline->traffic_usage;

                        if (isset($inbound->traffic_limit)) {
                            $inbound = self::updateInboundRemainingTraffic($inbound, $outlineBandwidth);
                        }

                        $outline->traffic_usage = $outlineTotalBandwidth;

                        $outline->save();

                        if ($inbound->is_active === '0' && !is_null($inbound->server)) {
                            OutlineService::delete($inbound->id);
                        }
                    }
                }

                /**
                 * If remaining day is 0, deactivate the inbound on the database.
                 */
                if (isset($inbound->expires_at)) {
                    $inbound = self::checkInboundExpiry($inbound);
                }

                $inbound->save();
            });
        }
    }

    private static function updateInboundRemainingTraffic(Inbound $inbound, float $bandwidthUsage): Inbound
    {
        // Update the remaining traffic limit of the inbound
        $remainingTraffic = $inbound->remaining_traffic - $bandwidthUsage;
        $inbound->remaining_traffic = $remainingTraffic > 0 ? $remainingTraffic : 0;
        $inbound->is_active = $inbound->remaining_traffic > 0 ? '1' : '0';

        return $inbound;
    }

    private static function checkInboundExpiry($inbound): Inbound
    {
        $expires_at = Carbon::parse($inbound->expires_at)->endOfDay();
        $today = Carbon::now()->endOfDay();
        $diff = $expires_at->diffInDays($today);
        $remainingDays = $today->greaterThan($expires_at) ? 0 : $diff;

        // If the inbound is not already deactivated, set its activation based on the remaining days
        if ($inbound->is_active !== '0') {
            $inbound->is_active = $remainingDays > 0 ? '1' : '0';
        }

        return $inbound;
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
