<?php

declare(strict_types=1);

namespace App\Controller;

use App\VeSync;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(['version' => '0.0.1']);
    }

    public function leaving(): Response
    {
        $client = new Client(['base_uri' => 'https://smartapi.vesync.com/']);
        $vesync = new VeSync($client);
        $token = $vesync->login('contato@fernandocarletti.net', 'xetD$Rjhi9ZPPhqj');
        $devices = $vesync->getDevices($token);
        $vesync->turnOn($token, $devices[0]);

        return new Response();
    }

    public function arriving(): Response
    {
        $client = new Client(['base_uri' => 'https://smartapi.vesync.com/']);
        $vesync = new VeSync($client);
        $token = $vesync->login('contato@fernandocarletti.net', 'xetD$Rjhi9ZPPhqj');
        $devices = $vesync->getDevices($token);
        $vesync->turnOff($token, $devices[0]);

        return new Response();
    }
}
