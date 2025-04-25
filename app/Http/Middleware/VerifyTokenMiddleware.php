<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class VerifyTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $client = new Client();
        $token = $request->bearerToken();
        $response = $client->request('GET', 'http://3.137.161.115:82/api/verify-token', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getStatusCode(), true);
        if ($data == 200) {
            return $next($request);
        }
        return response()->json([
            'message' => 'Usuário não logado.'
        ], 401);
    }
}
