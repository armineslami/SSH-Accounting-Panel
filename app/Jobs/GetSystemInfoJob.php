<?php

namespace App\Jobs;

use App\Models\SystemInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetSystemInfoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Run script file and save its result
        $scriptPath = base_path('app/Scripts/System.sh');
        $command = "$scriptPath 2>&1";
        $output = shell_exec($command);
        $systemInfoArray = json_decode($output);

        // Get the only row of data from database
        $lastSystemInfo = SystemInfo::first();

        // Always delete old data because we only need one row of data in the database
        if ($lastSystemInfo) {
            $lastSystemInfo->delete();
        }

        // Create a new model and add it to the database
        SystemInfo::create([
            'cpuUsage' => $systemInfoArray->cpuUsage,
            'memory' => $systemInfoArray->memory,
            'memoryUsage' => $systemInfoArray->memoryUsage,
            'swap' => $systemInfoArray->swap,
            'swapUsage' => $systemInfoArray->swapUsage,
            'disk' => $systemInfoArray->disk,
            'diskUsage' => $systemInfoArray->diskUsage,
            'upTime' => $systemInfoArray->upTime
        ]);
    }
}
