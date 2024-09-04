<?php

namespace App\Interfaces;

use App\Models\Outline;

interface OutlineRepositoryInterface
{
    public static function create(int $outlineId, string $keyName, string $key, int $inboundId, int $serverId);
    public static function byId(int $id): ?Outline;
    public static function byOutlineId(int $outlineId): ?Outline;
    public static function byInboundId(int $inboundId): ?Outline;
    public static function deleteById(int $id): int;
}
