<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class IntercessionController extends Controller
{

    private $googleApiKey = 'AIzaSyBscAm8DFRRyyGsyCWcINDhYt03PYmPwDg';

    private function getRouteFromGoogleMaps($origin, $destination)
    {
        $client = new Client();
        $url = "https://maps.googleapis.com/maps/api/directions/json";

        $response = $client->get($url, [
            'query' => [
                'origin' => $origin,
                'destination' => $destination,
                'key' => $this->googleApiKey,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data['status'] === 'OK') {
            $distance = $data['routes'][0]['legs'][0]['distance']['value']; // Distância em metros
            return $distance / 1000; // Retorna em quilômetros
        }

        return null; // Retorna null se a rota não for encontrada
    }

    public function findRoutes(Request $request, $travel_id)
    {
        $client = new \GuzzleHttp\Client();
        $token = $request->bearerToken();

        // Obtenha as viagens disponíveis
        $response = $client->request('GET', 'http://35.174.5.208:83/api/all-travel', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $availableTravels = $data['travel'];

        // Obtenha a viagem do viajante
        $traveler = Travel::where('id_travel', $travel_id)
            ->with(['arrival', 'output'])
            ->first();

        if (!$traveler) {
            return response()->json(['error' => 'Travel not found'], 404);
        }

        // Criar o grafo com base no Google Maps
        $grafo = [];
        // Adicionar nós para o viajante
        $startCity = $traveler->output->id_output;
        $endCity = $traveler->arrival->id_arrival;

        // Garantir que os nós de início e fim do viajante existam no grafo
        $grafo[$startCity] = [];
        $grafo[$endCity] = [];

        // Conectar ponto de saída do viajante aos pontos de saída dos pacotes
        foreach ($availableTravels as $travel) {
            $from = $travel['output'];
            $to = $travel['arrival'];

            $fromId = $from['id_output'];
            $toId = $to['id_arrival'];

            $origin = "{$from['latitude']},{$from['longitude']}";
            $destination = "{$to['latitude']},{$to['longitude']}";

            // Calcular distância entre o ponto de saída do viajante e o ponto de saída do pacote
            $distanceToPackage = $this->getRouteFromGoogleMaps("{$traveler->output->latitude},{$traveler->output->longitude}", $origin);
            if ($distanceToPackage !== null) {
                $grafo[$startCity][$fromId] = $distanceToPackage;
            }

            // Adicionar distância entre os pontos de saída e chegada do pacote
            $distancePackage = $this->getRouteFromGoogleMaps($origin, $destination);
            if ($distancePackage !== null) {
                $grafo[$fromId][$toId] = $distancePackage;
            }

            // Conectar ponto de chegada do pacote ao ponto de chegada do viajante
            $distanceToDestination = $this->getRouteFromGoogleMaps($destination, "{$traveler->arrival->latitude},{$traveler->arrival->longitude}");
            if ($distanceToDestination !== null) {
                $grafo[$toId][$endCity] = $distanceToDestination;
            }
        }

        // Calcular as distâncias usando Dijkstra
        $distances = $this->dijkstra($grafo, $startCity);

        // Verificar se há caminho disponível para o destino final
        if (!isset($distances[$endCity]) || $distances[$endCity] === INF) {
            return response()->json(['error' => 'No deliverable package found for this trip.'], 404);
        }

        // Filtrar entregas viáveis com a nova regra
        $reachableTravels = collect($availableTravels)->filter(function ($travel) use ($distances, $startCity, $endCity) {
            $packageStart = $travel['output']['id_output'];
            $packageEnd = $travel['arrival']['id_arrival'];

            // Verificar se os pontos do pacote estão alcançáveis
            if (!isset($distances[$packageStart]) || !isset($distances[$packageEnd])) {
                return false;
            }

            // Calcular o aumento de distância causado pelo pacote
            $originalDistance = $distances[$endCity];
            $distanceWithPackage = $distances[$packageStart] + $distances[$packageEnd];

            // Verificar se o aumento é maior que 100 km
            $distanceIncrease = $distanceWithPackage - $originalDistance;
            return $distanceIncrease <= 700; // Permitir somente pacotes que aumentem até 100 km
        });

        // Retornar os pacotes filtrados
        return response()->json($reachableTravels->values());
    }

    function dijkstra($graph, $start) {
        $distances = [];
        $previous = [];
        $queue = [];

        foreach ($graph as $vertex => $edges) {
            $distances[$vertex] = INF;
            $previous[$vertex] = null;
            $queue[$vertex] = INF;
        }

        $distances[$start] = 0;
        $queue[$start] = 0;

        while (!empty($queue)) {
            asort($queue);
            $current = key($queue);
            unset($queue[$current]);

            if ($distances[$current] === INF) {
                break;
            }

            foreach ($graph[$current] as $neighbor => $cost) {
                $alt = $distances[$current] + $cost;

                if (!isset($distances[$neighbor])) {
                    dd("Vértice não encontrado no grafo: {$neighbor}");
                }

                if ($alt < $distances[$neighbor]) {
                    $distances[$neighbor] = $alt;
                    $previous[$neighbor] = $current;
                    $queue[$neighbor] = $alt;
                }
            }
        }

        return $distances;
    }
}
