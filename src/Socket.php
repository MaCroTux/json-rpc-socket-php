<?php

namespace SocketServer;

use Kraken\Ipc\Socket\SocketListener;
use Kraken\Loop\Loop;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Throwable\Exception\Logic\InstantiationException;

class Socket
{
    private const PROTOCOL = 'tcp';

    /** @var Loop */
    private $loop;
    /** @var ServerProtocol */
    private $protocol;
    /** @var SocketConfig */
    private $socketConfig;

    public function __construct(
        Loop $loop,
        ServerProtocol $protocol,
        SocketConfig $socketConfig
    ) {
        $this->loop         = $loop;
        $this->protocol     = $protocol;
        $this->socketConfig = $socketConfig;
    }

    public static function createLoop(): Loop
    {
        return new Loop(new SelectLoop);
    }

    public static function buildFromProtocolAndAddress(
        Loop $loop,
        ServerProtocol $protocol,
        string $address,
        string $port
    ): self {
        return new self(
            $loop,
            $protocol,
            SocketConfig::buildFromAddress($address, $port)
        );
    }

    /**
     * @return Listener
     * @throws InstantiationException
     */
    public function listener(): Listener
    {
        try {
            return new Listener(
                new SocketListener(
                    $this->endpoint(),
                    $this->loop
                ),
                $this->protocol
            );
        } catch (InstantiationException $e) {
            throw $e;
        }
    }

    public function endpoint(): string
    {
        return self::PROTOCOL . '://' .
            $this->socketConfig->address() . ':' .
            $this->socketConfig->port();
    }

    public function port(): int
    {
        return $this->socketConfig->port();
    }

    public function loop(): Loop
    {
        return $this->loop;
    }
}
