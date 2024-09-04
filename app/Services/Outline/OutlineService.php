<?php

namespace App\Services\Outline;

use App\Models\Inbound;
use App\Models\Outline;
use App\Repositories\InboundRepository;
use App\Repositories\OutlineRepository;
use App\Repositories\ServerRepository;
use GuzzleHttp\Exception\GuzzleException;
use OutlineManagerClient\OutlineClient;
use OutlineManagerClient\Type\KeyType;

/**
 * Outline server repository
 * @link https://github.com/Jigsaw-Code/outline-server/blob/master/src/shadowbox/README.md
 */

class OutlineService
{
    public static function create(Inbound $inbound): KeyType
    {
        // Create an outline api
        $api = self::createClient($inbound->server->address);

        // Delete any existing key which its name is equal to the inbound username
        try {
            $keysList = $api->getKeys();
            foreach ($keysList as $id => $key) {
                if ($key->getName() === $inbound->username) {
                    $api->deleteKey($key->getId());
                    break;
                }
            }
        }
        catch (GuzzleException|\JsonException $e) {}


        // Create new key with name set to username
        $key = $api->addKey($inbound->username);

//        // Set key data limit to 0 bytes if inbound is not active
//        if ($inbound->is_active === '0') {
//            /**
//             * I'm setting data limit to 0 instead of using $api->deleteKey($key->getId())
//             * because I don't want the key to be changed if user gets activated in the future.
//             */
//            $api->setKeyDataLimit($key->getId(), 0);
//        }
//        else {
////            if (isset($inbound->traffic_limit)) {
////                // Set key data limit in bytes equal to traffic limit
////                $api->setKeyDataLimit($key->getId(), $inbound->traffic_limit * 1000 * 1000 * 1000);
////            }
////            else {
////                $api->unsetKeyDataLimit($key->getId());
////            }
//            /**
//             * Remove any limitation for the key.
//             * {@link UpdateBandwidthUsage} will take care of bandwidth usage update.
//             */
//            $api->unsetKeyDataLimit($key->getId());
//        }

        // Add the new outline to the database
        self::addToDatabase($inbound->id, $inbound->server->id, $key);

        return $key;
    }

    public static function update(Inbound $inbound, bool $hasOutline): KeyType|null
    {
        $api = self::createClient($inbound->server->address);

        // Check if there is already an outline connection
        $outline = OutlineRepository::byInboundId($inbound->id);

        if (!is_null($outline)) { // Outline found
            // If outline is not set, delete the current outline connection
            if (!$hasOutline || $inbound->is_active === '0') {
                $api->deleteKey($outline->outline_id);
                OutlineRepository::deleteById($outline->id);
            }
//            else if ($inbound->is_active === '0') {
//                $api->setKeyDataLimit($outline->outline_id, 0);
//            }
//            else  {
////                if (isset($inbound->traffic_limit)) {
////                    $api->setKeyDataLimit($outline->outline_id, $inbound->traffic_limit * 1000 * 1000 * 1000);
////                }
////                else {
////                    $api->unsetKeyDataLimit($outline->outline_id);
////                }
//                /**
//                 * Remove any limitation for the key.
//                 * {@link UpdateBandwidthUsage} will take care of bandwidth usage update.
//                 */
//                $api->unsetKeyDataLimit($outline->outline_id);
//            }

            return $api->getKeyById($outline->outline_id);
        }
        else if ($hasOutline && $inbound->is_active === '1') {
            // Delete any existing key which its name is equal to the inbound username
            try {
                $keysList = $api->getKeys();
                foreach ($keysList as $id => $key) {
                    if ($key->getName() === $inbound->username) {
                        $api->deleteKey($key->getId());
                        break;
                    }
                }
            }
            catch (GuzzleException|\JsonException $e) {}

            // Create new key with name set to username
            $key = $api->addKey($inbound->username);

//            if ($inbound->is_active === '0') {
//                $api->setKeyDataLimit($key->getId(), 0);
//            }
//            // Set key data limit in bytes equal to traffic limit
//            else if (isset($inbound->traffic_limit)) {
//                $api->setKeyDataLimit($key->getId(), $inbound->traffic_limit * 1000 * 1000 * 1000);
//            }

            // Add the new outline to the database
            self::addToDatabase($inbound->id, $inbound->server->id, $key);

            return $key;
        }

        return null;
    }

    public static function delete(int $inboundId): void
    {
        $inbound = InboundRepository::byId($inboundId);
        $api = self::createClient($inbound->server->address);
        $api->deleteKey($inbound->outline->outline_id);
    }

    public static function updateDataLimit(string $serverAddress, int $outlineId, int $traffic): void
    {
        $api = self::createClient($serverAddress);
        $api->setKeyDataLimit($outlineId, $traffic * 1000 * 1000 * 1000);
    }

    public static function getUsedTrafficForKeyInGB(string $serverAddress, int $outlineId): Float
    {
        $api = self::createClient($serverAddress);
        try {
            $usedBytes = $api->getUsedBytes();
            foreach ($usedBytes as $keyId => $usedByte) {
                if ($keyId === $outlineId) {
                    return round(($usedByte / 1000 / 1000 / 1000), 2);
                }
            }
        } catch (GuzzleException|\JsonException $e) {
            return 0;
        }

        return 0;
    }

    private static function createClient(string $serverIp): OutlineClient
    {
        // Find inbound server address
        $server = ServerRepository::byAddress($serverIp);

        // Create a new outline api
        return new OutlineClient($server->outline_api_url."/");
    }

    public static function deleteAllKeys(string $serverIp): void
    {
        // Create an outline api
        $api = self::createClient($serverIp);

        // Delete all existing keys
        $keysList = $api->getKeys();
        foreach ($keysList as $id => $key) {
            $api->deleteKey($key->getId());
        }
    }

    private static function addToDatabase(int $inboundId, int $serverId, KeyType $key): Outline
    {
        return OutlineRepository::create(
            outlineId: $key->getId(),
            keyName: $key->getName(),
            key: $key->getAccessUrl(),
            inboundId: $inboundId,
            serverId: $serverId
        );
    }

}
