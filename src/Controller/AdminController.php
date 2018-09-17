<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Exception\DomainException;
use App\VeSync;
use App\VeSync\Exception\VeSyncException;
use Linio\Component\Util\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController
{
    public function register(Request $request, Database $database, Vesync $vesync): Response
    {
        $data = Json::decode($request->getContent());

        try {
            if (empty($data['username']) || empty($data['password'])) {
                throw new DomainException('Invalid VeSync credentials.');
            }

            $token = $vesync->login($data['username'], $data['password']);
        } catch (DomainException | VeSyncException $e) {
            return new JsonResponse(['error' => ['message' => 'Invalid VeSync credentials.']]);
        }

        // Keep the old secret when refreshing VeSync token.
        try {
            $oldToken = $database->retrieveToken($token->getAccountId());
            $token->setSecret($oldToken->getSecret());
        } catch (DomainException $e) {
            $token->setSecret(sha1(uniqid() . random_bytes(20)));
        }

        $database->storeToken($token);

        return new Response(Response::HTTP_CREATED);
    }
}
