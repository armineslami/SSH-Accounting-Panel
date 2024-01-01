<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class BaseController extends Controller
{
    public function redirectIfFailed(string $to, string $status, string $response, int $id = null): RedirectResponse | null
    {
        $json = json_decode($response, true);

        if (!$response || !isset($json['code']) || $json['code'] === '0') {
            echo "WTF => $to";
            return Redirect::route($to, $id)->with([
                'status' => $status,
                'message' => $json['message'] ?? null
            ])->withInput();
        }

        return null;
    }
}
