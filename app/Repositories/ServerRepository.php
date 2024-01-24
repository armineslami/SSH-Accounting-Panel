<?php

namespace App\Repositories;

use App\Interfaces\ServerRepositoryInterface;
use App\Models\Server;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ServerRepository implements ServerRepositoryInterface
{
    public static function byId($id): ?Server
    {
        return Server::find($id);
    }

    public static function byAddress($address): ?Server
    {
        return Server::where("address", $address)->first();
    }

    public static function all(): Collection
    {
        return Server::all();
    }

    public static function paginate($count = 20): LengthAwarePaginator
    {
        return Server::paginate($count);
    }

    public static function count(): int
    {
        return Server::count();
    }

    public static function create(string $name, string $address, string $username, int $port, int $udp_port): Server
    {
        return Server::create([
            'name' => $name,
            'address' => $address,
            'username' => $username,
            'port' => $port,
            'udp_port' => $udp_port
        ]);
    }

    public static function update(int $id, mixed $server): bool
    {
        return Server::findOrFail($id)->update($server);
    }

    public static function deleteById(int $id): int
    {
        return Server::destroy($id);
    }
}
