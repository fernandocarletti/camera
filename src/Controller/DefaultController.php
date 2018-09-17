<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Exception\DomainException;
use App\VeSync;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController
{
    const STATUS_ON = 'on';
    const STATUS_OFF = 'off';

    public function index(): JsonResponse
    {
        return new JsonResponse(['version' => '0.0.1']);
    }

    public function leaving(Request $request, Database $database, VeSync $veSync): Response
    {
        try {
            $this->switchDevice($request, $database, $veSync, self::STATUS_ON);
        } catch (DomainException $e) {
            return new JsonResponse(['error' => ['message' => $e->getMessage()]]);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function arriving(Request $request, Database $database, VeSync $veSync): Response
    {
        try {
            $this->switchDevice($request, $database, $veSync, self::STATUS_OFF);
        } catch (DomainException $e) {
            return new JsonResponse(['error' => ['message' => $e->getMessage()]]);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    protected function switchDevice(Request $request, Database $database, VeSync $veSync, string $status): void
    {
        $accountId = $request->headers->get('x-account-id', null);
        $secret = $request->headers->get('x-secret', null);

        $token = $database->retrieveToken($accountId);
        if ($secret != $token->getSecret()) {
            throw new DomainException('Invalid credentials.');
        }

        $devices = $veSync->getDevices($token);

        foreach ($devices as $device) {
            if ($status == self::STATUS_ON) {
                $veSync->turnOn($token, $device);
            } else {
                $veSync->turnOff($token, $device);
            }
        }
    }
}
