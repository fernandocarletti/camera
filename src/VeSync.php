<?php

declare(strict_types=1);

namespace App;

use App\VeSync\Device;
use App\VeSync\Exception\VeSyncException;
use App\VeSync\Token;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Linio\Component\Util\Json;

class VeSync
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @throws VeSyncException
     */
    public function login(string $username, string $password): Token
    {
        $response = $this->client->post('vold/user/login', [
            'json' => [
                'account' => $username,
                'password' => md5($password),
            ],
        ]);

        $data = Json::decode((string) $response->getBody());
        $this->handleError($response, $data);

        $token = new Token();
        $token->setAccountId($data['accountID']);
        $token->setToken($data['tk']);

        return $token;
    }

    public function getDevices(Token $token): array
    {
        $response = $this->client->get('vold/user/devices', [
            'headers' => $token->toArray(),
        ]);

        $data = Json::decode((string) $response->getBody());
        $this->handleError($response, $data);
        $devices = [];

        foreach ($data as $rawDevice) {
            $device = new Device();
            $device->setId($rawDevice['cid']);
            $device->setName($rawDevice['deviceName']);
            $device->isOn($rawDevice['deviceStatus'] == 'on');

            $devices[] = $device;
        }

        return $devices;
    }

    public function turnOn(Token $token, Device $device): void
    {
        $response = $this->client->put(sprintf('v1/wifi-switch-1.3/%s/status/on', $device->getId()), [
            'headers' => $token->toArray(),
        ]);

        $data = Json::decode((string) $response->getBody());
        $this->handleError($response, $data);
    }

    public function turnOff(Token $token, Device $device): void
    {
        $response = $this->client->put(sprintf('v1/wifi-switch-1.3/%s/status/off', $device->getId()), [
            'headers' => $token->toArray(),
        ]);

        $data = Json::decode((string) $response->getBody());
        $this->handleError($response, $data);
    }

    private function handleError(Response $response, ?array $data): void
    {
        if ($response->getStatusCode() != 200) {
            if (!isset($data['error'])) {
                throw new VeSyncException((string) $response->getBody());
            }
            throw new VeSyncException($data['error']['msg'], $data['error']['code']);
        }
    }
}
