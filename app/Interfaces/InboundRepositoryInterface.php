<?php

namespace App\Interfaces;

use App\Models\Inbound;
use Illuminate\Pagination\LengthAwarePaginator;

interface InboundRepositoryInterface
{
    public static function byId($id): ?Inbound;
    public static function paginate($count = 20): LengthAwarePaginator;
    public static function count(): int;
    public static function create(string $username, string $password, string $is_active, float $traffic_limit = null, int $max_login, string $server_ip, string $expires_at = null): Inbound;
    public static function update(int $id, mixed $inbound): bool;
    public static function deleteById(int $id): int;
    public static function search(?string $query): LengthAwarePaginator;
}
