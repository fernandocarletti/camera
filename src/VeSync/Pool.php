<?php

declare(strict_types=1);

namespace App\VeSync;

class Pool
{
    /**
     * @var string
     */
    protected $deviceId;

    /**
     * @var Token[]
     */
    protected $tokens;

    public function __construct(string $deviceId)
    {
        $this->deviceId = $deviceId;
    }

    public function addToken(Token $token): void
    {
        $this->tokens[] = $token;
    }

    /**
     * @return Token[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }
}
