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

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function setAccountId(string $accountId): void
    {
        $this->accountId = $accountId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function toArray(): array
    {
        return [
            'accountID' => $this->getAccountId(),
            'tk' => $this->getToken(),
        ];
    }
}
