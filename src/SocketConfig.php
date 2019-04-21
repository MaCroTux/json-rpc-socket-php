<?php

namespace SocketServer;

class SocketConfig
{
    /** @var string */
    private $address;
    /** @var int */
    private $port;

    private function __construct(string $address, int $port)
    {
        $this->address = $address;
        $this->port    = $port;
    }

    public static function buildFromAddress(string $address, int $port): self
    {
        return new self($address, $port);
    }

    public function address(): string
    {
        return $this->address;
    }

    public function port(): int
    {
        return $this->port;
    }
}
