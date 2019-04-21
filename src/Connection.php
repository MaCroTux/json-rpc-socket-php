<?php

namespace SocketServer;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;

class Connection
{
    /** @var string */
    private $id;
    /** @var SocketListener */
    private $server;
    /** @var SocketInterface */
    private $client;
    /** @var Listener */
    private $listener;
    /** @var \DateTime  */
    private $date;
    /** @var ServerProtocol */
    private $protocol;

    public function __construct(
        SocketListener $server,
        SocketInterface $client,
        Listener $listener,
        ServerProtocol $protocol
    ) {
        $this->server   = $server;
        $this->client   = $client;
        $this->listener = $listener;
        $this->date     = new \DateTime();
        $this->protocol = $protocol;

        $this->id = self::uniqueConnectionId($server, $client);
    }

    public function localPort(): int
    {
        return $this->server->getLocalPort();
    }

    public static function uniqueConnectionId(
        SocketListener $server,
        SocketInterface $client
    ): string {
        return $server->getResourceId() . '#' .
            $client->getResourceId() . '#' .
            $server->getLocalPort();
    }

    public static function uniqueConnectionIdFromClientId(
        SocketListener $server,
        int $clientId
    ): string {
        return $server->getResourceId() . '#' .
            $clientId . '#' .
            $server->getLocalPort();
    }

    public function protocolOnConnect(): void
    {
        $this->protocol->onConnect($this->server, $this->client);
    }

    public function protocolOnData(): void
    {
        $this->client()->on(
            'data',
            function (SocketInterface $client, string $query) {
                $this->protocol->onData($this, $query);
            }
        );
    }

    public function client(): SocketInterface
    {
        return $this->client;
    }

    public function closeConnect(): void
    {
        $this->listener->closeConnection(
            $this->server,
            $this->client->getResourceId()
        );
    }

    public function kickConnect($clientId): void
    {
        $this->listener->closeConnection(
            $this->server,
            $clientId
        );
    }

    public function getAllConnections()
    {
        $connections = $this->listener->getConnections();
        return array_map(function() {
            return [
                'isOpen'   => $this->client->isOpen(),
                'clientId' => $this->client->getResourceId(),
                'port'     => $this->client->getLocalPort(),
                'date'     => $this->date->format('d/m/Y H:i:s'),
            ];
        }, $connections);
    }

    public function id(): string
    {
        return $this->id;
    }
}
