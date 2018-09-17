<?php

declare(strict_types=1);

namespace App\VeSync;

class Device
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isOn;

    public function __construct(string $id, string $name, bool $isOn)
    {
        $this->id = $id;
        $this->name = $name;
        $this->isOn = $isOn;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isOn(bool $isOn = null): bool
    {
        if ($isOn !== null) {
            $this->isOn = $isOn;

            return $isOn;
        }

        return $this->isOn;
    }
}
