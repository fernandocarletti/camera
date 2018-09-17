<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Exception\DomainException;
use App\VeSync;
use App\VeSync\Token;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(['version' => '0.0.1']);
    }

    public function leaving(Request $request, Database $database, VeSync $veSync): Response
    {
        try {
            $token = $this->switchTokenStatus($request, $database, true);
            $this->switchDevices($token, $database, $veSync);
        } catch (DomainException $e) {
            return new JsonResponse(['error' => ['message' => $e->getMessage()]]);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function arriving(Request $request, Database $database, VeSync $veSync): Response
    {
        try {
            $token = $this->switchTokenStatus($request, $database, false);
            $this->switchDevices($token, $database, $veSync);
        } catch (DomainException $e) {
            return new JsonResponse(['error' => ['message' => $e->getMessage()]]);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    protected function switchTokenStatus(Request $request, Database $database, bool $isAway): Token
    {
        $accountId = $request->headers->get('x-account-id', null);
        $secret = $request->headers->get('x-secret', null);

        $token = $database->retrieveToken($accountId);
        if ($secret != $token->getSecret()) {
            throw new DomainException('Invalid credentials.');
        }

        $token->isAway($isAway);
        $database->storeToken($token);

        return $token;
    }

    protected function switchDevices(Token $token, Database $database, VeSync $veSync): void
    {
        $devices = $veSync->getDevices($token);

        foreach ($devices as $device) {
            try {
                $pool = $database->retrievePool($device);
            } catch (DomainException $e) {
                $pool = null;
            }

            if ($pool === null) {
                $allAway = $token->isAway();
            } else {
                $allAway = true;

                foreach ($pool->getTokens() as $token) {
                    $allAway = $allAway && $token->isAway();
                }
            }

            if ($allAway) {
                $veSync->turnOn($token, $device);
            } else {
                $veSync->turnOff($token, $device);
            }
        }
    }
}
