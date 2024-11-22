<?php

namespace App\Http\Controllers\api;

use App\Http\Algorithm\Dijkstra;
use App\Http\Controllers\Controller;
use App\Models\Travel;
use App\Services\GoogleMapsService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class IntercessionController extends Controller
{
    protected $googleMaps;

    public function __construct(GoogleMapsService $googleMaps)
    {
        $this->googleMaps = $googleMaps;
    }

    public function findRoutes(Request $request, $travel_id)
    {
        try {
            $client = new Client();
            $token = $request->bearerToken();
            $response = $client->request('GET', 'http://35.174.5.208:83/api/all-travel', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            $packages = $data['travel'];
            $traveler = Travel::where('id_travel', '=', $travel_id)
                ->with(['arrival', 'output'])->get()->first();

            $graph = $this->buildGraph($packages);

            $dijkstra = new Dijkstra($graph);

            $matches = [];

            $travelerPath = $dijkstra->shortestPath($traveler['output_id'], $traveler['arrival_id']);

            foreach ($packages as $package) {
                if ($traveler['id_travel'] !== $package['id_travel']) {
                    $packagePath = $dijkstra->shortestPath($package['output_id'], $package['arrival_id']);

                    if ($this->isRouteFeasible($travelerPath, $packagePath)) {
                        $matches[] = [
                            'traveler' => $traveler,
                            'package' => $package,
                        ];
                    }
                }
            }

            return response()->json($matches);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error has occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function buildGraph($packages)
    {
        $graph = [];
        foreach ($packages as $package) {
            $distance = $this->googleMaps->getDistance(
                "{$package['output']['latitude']},{$package['output']['longitude']}",
                "{$package['arrival']['latitude']},{$package['arrival']['longitude']}"
            );
            $graph[$package['output_id']][$package['arrival_id']] = $distance;
        }

        return $graph;
    }

    private function isRouteFeasible($travelerPath, $packagePath)
    {
        return !array_diff($packagePath, $travelerPath);
    }
}
