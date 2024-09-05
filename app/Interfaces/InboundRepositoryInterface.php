<?php

namespace App\Interfaces;

use App\Models\Inbound;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface InboundRepositoryInterface
{
    public static function byId($id): ?Inbound;
    public static function byUsername($username): ?Inbound;
    public static function all(): Collection;
    public static function paginate($count = 20): LengthAwarePaginator;
    public static function count(): int;
    public static function create(string $username, string $password, string $is_active, float $traffic_limit = null,
                                  float $remaining_traffic = null, int $max_login, string $server_ip, string
                                  $expires_at = null): Inbound;
    public static function update(int $id, mixed $inbound): bool;
    public static function deleteById(int $id): int;
    public static function search(?string $query, int $paginate): LengthAwarePaginator;
}
