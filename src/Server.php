<?php

namespace SocketServer;

use Kraken\Loop\Loop;

class Server
{
    /** @var Socket[] */
    private $sockets;
    /** @var Loop  */
    private $loop;

    /**
     * Server constructor.
     * @param Socket $socket
     */
    private function __construct(Socket $socket)
    {
        $this->loop = $socket->loop();
        $this->sockets[$socket->port()] = $socket;
    }

    public static function buildFromProtocolAndPort(
        ServerProtocol $protocol,
        int $port,
        string $address = '0.0.0.0'
    ): self {
        $loop   = Socket::createLoop();
        $socket = Socket::buildFromProtocolAndAddress(
            $loop,
            clone $protocol,
            $address,
            $port
        );

        return new self($socket);
    }

    /**
     * @param ServerProtocol $protocol
     * @param string $address
     * @param int $port
     * @throws \Exception
     */
    public function addProtocolAndPort(
        ServerProtocol $protocol,
        int $port,
        string $address = '0.0.0.0'
    ): void {
        $socket = Socket::buildFromProtocolAndAddress(
            $this->loop,
            clone $protocol,
            $address,
            $port
        );

        if (empty($this->sockets[$socket->port()])) {
            $this->sockets[$socket->port()] = $socket;
            return;
        }

        throw new \Exception('Port ' . $socket->port() . ' is in used');
    }

    public function start() {
        $sockets = $this->sockets;
        foreach ($sockets as $socket) {
            $this->loop->onStart(
                function() use ($socket) {
                    $listener = $socket->listener();
                    $listener->start();
                }
            );
        }

        $this->loop->start();
    }
}
