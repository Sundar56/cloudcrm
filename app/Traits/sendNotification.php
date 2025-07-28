<?php

namespace App\Traits;

use Exception;
use GuzzleHttp\Client;

trait sendNotification
{
    public function sendNotification($channel, $state)
    {
        try {
            $endPort           = env('SOCKET_PORT');
            $endUrl            = env('SOCKET_URL');
            $endBroadcast      = env('SOCKET_BRODCASTURL');
            $socketEnvironment = env('SOCKET_ENVIRONMENT');
            if ($socketEnvironment == 'localhost') {
                $endpoint    = $endUrl . ":" . $endPort . "/" . $endBroadcast;
            } else {
                $endpoint    = $endUrl . "/" . $endBroadcast;
            }
            $queryParams = http_build_query(['channel' => $channel, 'state' => $state]);
            $url         = "$endpoint?$queryParams";
            $headers = [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json'
            ];

            $client   = new Client();
            $response = $client->request('GET', $url, ['headers' => $headers]);

            return [
                'response' => json_decode($response->getBody(), true),
            ];
        } catch (Exception $e) {
            return ['statusCode' => $e->getCode(), 'message' => $e->getMessage(), 'body' => null];
        }
    }
}
