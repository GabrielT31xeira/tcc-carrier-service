<?php

namespace App\Services;

use GuzzleHttp\Client;

class GoogleMapsService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setDeveloperKey('AIzaSyBscAm8DFRRyyGsyCWcINDhYt03PYmPwDg');
    }

    public function getDistance($origin, $destination)
    {
        $response = $this->client->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'query' => [
                'origins' => $origin,
                'destinations' => $destination,
                'key' => $this->apiKey,
                'mode' => 'driving'
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data['status'] === 'OK') {
            return $data['rows'][0]['elements'][0]['distance']['value']; // Distância em metros
        }

        return null;
    }
}
