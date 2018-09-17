<?php

declare(strict_types=1);

namespace App;

use App\Exception\DomainException;
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
}
