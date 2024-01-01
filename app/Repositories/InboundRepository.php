<?php

namespace App\Repositories;

use App\Interfaces\InboundRepositoryInterface;
use App\Models\Inbound;
use Illuminate\Pagination\LengthAwarePaginator;

class InboundRepository implements InboundRepositoryInterface
{
    public static function byId($id): ?Inbound
    {
        return Inbound::find($id);
    }

    public static function paginate($count = 20): LengthAwarePaginator
    {
        return Inbound::paginate($count);
    }

    public static function count(): int
    {
        return Inbound::count();
    }

    public static function create(string $username, string $password, string $is_active, float $traffic_limit = null, int $max_login, string $server_ip, string $expires_at = null): Inbound
    {
        return Inbound::create([
            "username" => $username,
            "password" => $password,
            "is_active" => $is_active,
            "traffic_limit" => $traffic_limit,
            "max_login" => $max_login,
            "server_ip" => $server_ip,
            "expires_at" => $expires_at
        ]);
    }

    public static function update(int $id, mixed $inbound): bool
    {
        return Inbound::findOrFail($id)->update($inbound);
    }

    public static function deleteById(int $id): int
    {
        return Inbound::destroy($id);
    }

    public static function search(?string $query): LengthAwarePaginator
    {
        return Inbound::where("username", 'LIKE', '%' . $query . '%')
            ->orWhere("server_ip", 'LIKE', '%' . $query . '%')
            ->paginate();
    }
}
