<?php

namespace App\Services\Backup;

use App\Repositories\InboundRepository;
use App\Repositories\SettingRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    public static function createBackup(): Collection
    {
        $settings = SettingRepository::first();
        $inbounds = InboundRepository::all();
//        $servers = ServerRepository::all();

        $settingsArray = $settings->toArray();
        $inboundsArray = $inbounds->toArray();
//        $serversArray = $servers->toArray();

        $inboundsBackup = json_encode($inboundsArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $settingsBackup = json_encode($settingsArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
//        $serversBackup = json_encode($serversArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Storage::disk('public')->put('settings.json', $settingsBackup);
        Storage::disk('public')->put('inbounds.json', $inboundsBackup);
//        Storage::disk('public')->put('servers.json', $serversBackup);

        $zipFileName = "sap-backup-" . \Carbon\Carbon::now()->toDateTimeString() . ".zip";
        $zippedBackupPath = storage_path("app/public/" . $zipFileName);
        $zip = new ZipArchive();
        $zip->open($zippedBackupPath, ZipArchive::CREATE);
        $zip->addFile(storage_path('app/public/settings.json'), 'settings.json');
        $zip->addFile(storage_path('app/public/inbounds.json'), 'inbounds.json');
//        $zip->addFile(storage_path('app/public/servers.json'), 'servers.json');
        $zip->close();

        Storage::disk('public')->delete('settings.json');
        Storage::disk('public')->delete('inbounds.json');
//        Storage::disk('public')->delete('servers.json');

        return Collection::make([
            "path" => $zippedBackupPath,
            "name" => $zipFileName
        ]);
    }

    public static function extractBackup(UploadedFile $backup): array|null
    {
        $backupName = pathinfo($backup->getClientOriginalName(), PATHINFO_FILENAME);
        $extractPath = storage_path('app/extracted_backup');

        if (!file_exists($extractPath)) {
            mkdir($extractPath, 0777, true);
        }

        $zip = new ZipArchive;

        if ($zip->open($backup->path()) === true) {

            $zip->extractTo($extractPath);
            $zip->close();

            $settingsPath = $extractPath . "/" . $backupName . "/settings.json";
            $inboundsPath = $extractPath . "/" . $backupName . "/inbounds.json";
//            $serversPath = $extractPath . "/" . $backupName . "/servers.json";

            if (file_exists($inboundsPath) && file_exists($settingsPath) ) { // && file_exists($serversPath)
                $settingsContent = file_get_contents($settingsPath);
                $inboundsContent = file_get_contents($inboundsPath);
//                $serversContent = file_get_contents($serversPath);

                $settings = json_decode($settingsContent, true);
                $inbounds = json_decode($inboundsContent, true);
//                $servers = json_decode($serversContent, true);

                // Cleanup: Delete the uploaded ZIP file and the extracted files
                unlink($backup->path()); // Delete the uploaded ZIP file
                File::deleteDirectory($extractPath); // Recursively delete the extracted_files directory


                return [
                    "settings"  => $settings,
                    "inbounds"  => $inbounds,
//                    "servers"   => $servers
                ];
            }

            try {
                unlink($backup->path());
                File::deleteDirectory($extractPath);
            } catch (\Exception) {
            }

            return null;
        }

        return null;
    }
}
