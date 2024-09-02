<?php

namespace App\Interfaces;

use App\Models\Server;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ServerRepositoryInterface
{
    public static function byId($id): ?Server;
    public static function byAddress($address): ?Server;
    public static function all(): Collection;
    public static function paginate($count = 20): LengthAwarePaginator;
    public static function count(): int;
    public static function create(string $name, string $address, string $username, int $port, int $udp_port, string $outline_api_url): Server;
    public static function update(int $id, mixed $server): bool;
    public static function deleteById(int $id): int;
}
