<?php

declare(strict_types=1);

namespace App\VeSync;

class Token
{
    /**
     * @var string
     */
    protected $accountId;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $isAway;

    /**
     * @var string
     */
    protected $secret;

    public function __construct(string $accountId, string $token, bool $isAway, string $secret = null)
    {
        $this->accountId = $accountId;
        $this->token = $token;
        $this->isAway = $isAway;
        $this->secret = $secret;
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function isAway(bool $isAway = null): bool
    {
        if ($isAway !== null) {
            $this->isAway = $isAway;
        }

        return $this->isAway;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function toArray(): array
    {
        return [
            'accountId' => $this->getAccountId(),
            'token' => $this->getToken(),
            'isAway' => $this->isAway(),
            'secret' => $this->getSecret(),
        ];
    }

    public static function fromArray(array $data): Token
    {
        return new Token($data['accountId'], $data['token'], $data['isAway'], $data['secret']);
    }
}
