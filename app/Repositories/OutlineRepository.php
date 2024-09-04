<?php

namespace App\Repositories;

use App\Interfaces\OutlineRepositoryInterface;
use App\Models\Outline;

class OutlineRepository implements OutlineRepositoryInterface
{
    public static function create(int $outlineId, string $keyName, string $key, int $inboundId, int $serverId)
    {
        return Outline::create([
            "outline_id" => $outlineId,
            "key_name" => $keyName,
            "key" => $key,
            "inbound_id" => $inboundId,
            "server_id" => $serverId
        ]);
    }

    public static function byId(int $id): ?Outline
    {
        return Outline::find($id);
    }

    public static function byOutlineId(int $outlineId): ?Outline
    {
        return Outline::where("outline_id", $outlineId)->first();
    }

    public static function byInboundId(int $inboundId): ?Outline
    {
        return Outline::where("inbound_id", $inboundId)->first();
    }

    public static function deleteById(int $id): int
    {
        return Outline::destroy($id);
    }
}
