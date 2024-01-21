<?php

namespace App\Console\Commands;

use App\Repositories\SettingRepository;
use App\Services\Backup\BackupService;
use App\Services\Dropbox\DropboxService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Kunnu\Dropbox\Exceptions\DropboxClientException;

class BackupToDropbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-to-dropbox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a backup and uploads it to the dropbox';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $settings               = SettingRepository::first();
        $clientId               = $settings->dropbox_client_id;
        $clientSecret           = $settings->dropbox_client_secret;
        $dropboxToken           = $settings->dropbox_token;
        $dropboxRefreshToken    = $settings->dropbox_refresh_token;
        $dropboxExpireDate      = $settings->dropbox_token_expire_date;

        if (!$clientId || !$clientSecret || !$dropboxRefreshToken || !$dropboxExpireDate)
            return;

        if (Carbon::now()->diffInHours($dropboxExpireDate) >= 3) {
            $newAccessToken = DropboxService::refreshDropboxToken(
                clientId: $clientId,
                clientSecret: $clientSecret,
                refreshToken: $dropboxRefreshToken
            );

            if (!$newAccessToken) {
                return;
            }

            $dropboxToken = $newAccessToken->getToken();
        }

        $app = new DropboxApp($clientId, $clientSecret, $dropboxToken);

        try {
            $dropbox = new Dropbox($app);

        } catch (DropboxClientException $e) {
            $message = json_decode($e->getMessage(), true);
            $error = $message['error']['.tag'] ?: ($message['error'] ?? $e->getMessage());
            Log::error($error);
            return;
        }

        $backup = BackupService::createBackup();

        $dropboxFile = new DropboxFile($backup->get("path"));
        $dropboxPath = "/" . $backup->get("name");

        try {
            $searchResults = $dropbox->search("/", "sap-backup-");
            $items = $searchResults->getItems();
            foreach ($items as $item) {
                $metadata = $item->getMetadata();
                $dropbox->delete($metadata->path_display);
            }

            $dropbox->upload($dropboxFile, $dropboxPath, ['autorename' => true]);
            Storage::disk('public')->delete($backup->get("name"));
            Log::info("Saved backup to the dropbox as: " . $backup->get("name"));
        } catch (DropboxClientException $e) {
            $message = json_decode($e->getMessage(), true);
            $error = $message['error']['.tag'] ?: ($message['error'] ?? $e->getMessage());
            Log::error($error);
            return;
        }
    }
}
