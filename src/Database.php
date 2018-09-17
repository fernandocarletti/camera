<?php

declare(strict_types=1);

namespace App;

use App\Exception\DomainException;
use App\VeSync\Device;
use App\VeSync\Pool;
use App\VeSync\Token;
use Linio\Component\Util\Json;
use Predis\ClientInterface;

class Database
{
    /**
     * @var ClientInterface
     */
    protected $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function storeToken(Token $token): void
    {
        $this->client->set('vesync:account:' . $token->getAccountId(), Json::encode($token->toArray()));
    }

    public function retrieveToken(string $accountId): Token
    {
        $data = $this->client->get('vesync:account:' . $accountId);

        if (!$data) {
            throw new DomainException('Token not found.');
        }

        return Token::fromArray(Json::decode($data));
    }

    /**
     * @param string[] $accountId
     *
     * @return Token[]
     */
    public function retrieveTokens(array $accountIds): array
    {
        $keys = array_map(function ($element) { return 'vesync:account:' . $element; }, $accountIds);
        $values = $this->client->mget($keys);
        $tokens = [];

        foreach ($values as $value) {
            if (!$value) {
                continue;
            }

            $data = Json::decode($value);
            $tokens[] = Token::fromArray($data);
        }

        return $tokens;
    }

    public function retrievePool(Device $device): Pool
    {
        $value = $this->client->get('vesync:pool:' . $device->getId());

        if (!$value) {
            throw new DomainException('No pool found for device.');
        }

        $data = Json::decode($value);
        $tokens = $this->retrieveTokens($data['accountIds']);
        $pool = new Pool($device->getId());

        foreach ($tokens as $token) {
            $pool->addToken($token);
        }

        return $pool;
    }
}
