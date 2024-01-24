<?php

namespace App\Http\Controllers;

use App\Models\SystemInfo;
use App\Repositories\InboundRepository;
use App\Repositories\ServerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $inboundCount = InboundRepository::count();
        $serverCount  = ServerRepository::count();
        return view('dashboard', [
            'inboundCount' => $inboundCount,
            'serverCount'  => $serverCount
        ]);
    }

    public function index(): JsonResponse
    {
        return response()->json(['systemInfo' => SystemInfo::first()]);
    }
}
