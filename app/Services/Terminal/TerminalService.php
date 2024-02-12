<?php

namespace App\Services\Terminal;

use App\Models\TerminalSession;
use App\Repositories\InboundRepository;
use App\Repositories\ServerRepository;
use App\Services\Terminal\Command\Command;
use App\Services\Terminal\Message\Message;
use App\Utils\Utils;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TerminalService
{
    /**
     * @var false|resource|null
     */
    private static $process = null;

    private static bool $failed = false;

    public static function setup(): void
    {
        self::turnOffBuffer();
    }

    public static function run(TerminalSession $terminalSession): bool
    {
        $command = $terminalSession->command;

        $request = json_decode($terminalSession->request);

        $script = self::script($command, $request);

        if (is_null($script)) {
            self::$failed = true;
            echo Message::red("Failed to run the task");
            return self::$failed;
        }

        if (!empty($script)) {
            self::execute($script);
        }

        self::cleanup();

        if (!self::$failed) {
            self::updateDatabase($command, $request);
        }

        return self::$failed;
    }

    public static function failed(): bool
    {
        return self::$failed;
    }

    private static function turnOffBuffer(): void
    {
        // Turn off output buffering
        ini_set('output_buffering', 'off');
        // Turn off PHP output compression
        ini_set('zlib.output_compression', false);
        // Implicitly flush the buffer(s)
        ini_set('implicit_flush', true);
        ob_implicit_flush(true);

        // Clear, and turn off output buffering
        while (ob_get_level() > 0) {
            // Get the current level
            $level = ob_get_level();
            // End the buffering
            ob_end_clean();
            // If the current level has not changed, abort
            if (ob_get_level() == $level) break;
        }

        // Disable apache output buffering/compression
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
            apache_setenv('dont-vary', '1');
        }
    }

    private static function ipv4(): false|null|string
    {
        $ip = shell_exec("curl -s ipv4.icanhazip.com");
        if ($ip === null || $ip === false) {
            return $ip;
        }
        return trim($ip);
    }

    private static function script(string $command, mixed $request): string|null
    {
        $script     = self::scriptPath($command);
        $scriptDir  = self::scriptDir();
        $key        = self::keyPath();
        $localIp    = self::ipv4();

        if (is_null($request->server)) {
            return null;
        }

        $serverIp   = $request->server->address;

        if (is_null($localIp)) {
            return null;
        }

        if ($localIp !== $serverIp) {
            switch ($command) {
                case Command::TRANSFER_KEY:
                    return "bash -s < $script {$request->server->username} {$request->server->password} {$request->server->address} {$request->server->port} 2>&1";
                case Command::SET_UP_SERVER:
                    return "bash -s < $script {$request->server->udp_port} $scriptDir {$request->server->username} {$request->server->address} {$request->server->port} 2>&1";
                case Command::DELETE_SERVER:
                    return "sudo ssh -o StrictHostKeyChecking=accept-new -i {$key} -p {$request->server->port} {$request->server->username}@{$request->server->address} 'bash -s' < $script 2>&1";
                case Command::CREATE_INBOUND:
                case Command::UPDATE_INBOUND:
                    return "sudo ssh -o StrictHostKeyChecking=accept-new -i {$key} -p {$request->server->port} {$request->server->username}@{$request->server->address} 'export USERNAME={$request->inbound->username}; export PASSWORD={$request->inbound->user_password}; export IS_ACTIVE={$request->inbound->is_active}; export MAX_LOGIN={$request->inbound->max_login}; export ACTIVE_DAYS={$request->inbound->active_days}; export TRAFFIC_LIMIT={$request->inbound->traffic_limit}; bash -s' < {$script} 2>&1";
                case Command::DELETE_INBOUND:
                    return "sudo ssh -o StrictHostKeyChecking=accept-new -i {$key} -p {$request->inbound->server->port} {$request->inbound->server->username}@{$request->inbound->server->address} 'export USERNAME={$request->inbound->username}; bash -s' < $script 2>&1";
            }
        }
        else {
            switch ($command) {
                case Command::TRANSFER_KEY:
                    return ""; // No need to copy key for local server
                case Command::SET_UP_SERVER:
                    return "bash -s < $script {$request->server->udp_port} $scriptDir 2>&1";
                case Command::DELETE_SERVER:
                    return "bash -s < $script 2>&1";
                case Command::CREATE_INBOUND:
                case Command::UPDATE_INBOUND:
                    return "export USERNAME={$request->inbound->username}; export PASSWORD={$request->inbound->user_password}; export IS_ACTIVE={$request->inbound->is_active}; export MAX_LOGIN={$request->inbound->max_login}; export ACTIVE_DAYS={$request->inbound->active_days}; export TRAFFIC_LIMIT={$request->inbound->traffic_limit}; bash -s < {$script} 2>&1";
                case Command::DELETE_INBOUND:
                    return "export USERNAME={$request->inbound->username} bash -s < $script 2>&1";
            }
        }

        return null;
    }

    private static function scriptPath(string $command): string
    {
        return base_path("app/Scripts/".$command.".sh");
    }

    private static function scriptDir(): string
    {
        return base_path("app/Scripts");
    }

    private static function keyPath(): string
    {
        return base_path("storage/keys/ssh_accounting_panel");
    }

    private static function execute(string $command): void
    {
        $descriptorSpec = array(
            0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
            2 => array("pipe", "w")    // stderr is a pipe that the child will write to
        );

        flush();

        self::$process = proc_open($command, $descriptorSpec, $pipes);

        echo Message::green("PID: " . proc_get_status(self::$process)['pid']);

        echo "<div style='white-space: pre-wrap; line-height: 2.2rem;'>";

        if (is_resource(self::$process)) {
            $response = new StreamedResponse(function () use ($pipes) {
                while ($line = fgets($pipes[1])) {
                    self::checkAndPrint($line);
                    flush();
                }
            });
            $response->headers->set('Content-Type', 'text/event-stream');
            $response->headers->set('X-Accel-Buffering', 'no');
            $response->headers->set('Cache-Control', 'no-cache');
            $response->send();
        }

//        echo "</div>";
    }

    private static function checkAndPrint(string $text): void
    {
        echo $text;

        if (
            str_contains($text, "Failed to run the task") ||
            str_contains($text, "Operation timed out") ||
            str_contains($text, "Connection refused") ||
            str_contains($text, "Permission denied") ||
            str_contains($text, "Connection test failed") ||
            str_contains($text, "sudo: a terminal is required")
        ) {
            self::$failed = true;
//            echo "<p> </p>";
        }
    }

    private static function cleanup(): void
    {
        if (isset(self::$process)) {
            try {
                proc_close(self::$process);

            }
            catch (\Exception $error) {}

            self::$process = null;
        }
    }

    private static function updateDatabase(string $command, mixed $request): void
    {
        switch ($command) {
            case Command::SET_UP_SERVER:
                self::createServer($request->server);
                break;
            case Command::DELETE_SERVER:
                self::deleteServer($request->id);
                break;
            case Command::CREATE_INBOUND:
                self::createInbound($request->inbound);
                break;
            case Command::UPDATE_INBOUND:
                self::updateInbound($request->id, $request->inbound);
                break;
            case Command::DELETE_INBOUND:
                self::deleteInbound($request->id);
                break;
            default:
                break;
        }
    }

    private static function createServer(mixed $server): void
    {
        ServerRepository::create(
            $server->name,
            $server->address,
            $server->username,
            $server->port,
            $server->udp_port
        );
    }

    private static function deleteServer(int $id): void
    {
        ServerRepository::deleteById($id);
    }

    private static function createInbound(mixed $inbound): void
    {
        InboundRepository::create(
            username: $inbound->username,
            password: $inbound->user_password,
            is_active: $inbound->is_active,
            traffic_limit: $inbound->traffic_limit ?? null,
            remaining_traffic: $inbound->traffic_limit ?? null,
            max_login: $inbound->max_login,
            server_ip: $inbound->server_ip,
            expires_at: $inbound->active_days ? Carbon::now()->addDays($inbound->active_days) : null
        );
    }

    private static function updateInbound(int $id, mixed $inbound): void
    {
        $inbound->expires_at    = Utils::convertActiveDaysToExpireAtDate($inbound->active_days);
        $inbound->password      = $inbound->user_password;

        if ($inbound->traffic_limit < $inbound->remaining_traffic) {
            $inbound->remaining_traffic = $inbound->traffic_limit;
        }
        else if (isset($inbound->traffic_limit) && !isset($inbound->remaining_traffic)) {
            $inbound->remaining_traffic = $inbound->traffic_limit;
        }
        else if (isset($inbound->remaining_traffic) && !isset($inbound->traffic_limit)) {
            $inbound->remaining_traffic = $inbound->traffic_limit;
        }

        InboundRepository::update(
            $id,
            (array)$inbound
        );
    }

    private static function deleteInbound(int $id): void
    {
        InboundRepository::deleteById($id);
    }
}
