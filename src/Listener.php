<?php

namespace SocketServer;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;

class Listener
{
    /** @var SocketListener */
    private $socketListener;
    /** @var ServerProtocol */
    private $protocol;
    /** @var Connection[] */
    private $connections;

    public function __construct(
        SocketListener $socketListener,
        ServerProtocol $protocol
    ) {
        $this->socketListener = $socketListener;
        $this->protocol       = $protocol;
    }

    /**
     * @throws \Kraken\Throwable\Exception\Logic\InstantiationException
     */
    public function start(): void
    {
        $protocol = $this->protocol;
        $this->socketListener->on(
            'connect',
            function(SocketListener $server, SocketInterface $client) use ($protocol) {
                $connection = $this->createOnResumeConnection(
                    $server,
                    $client,
                    clone $protocol
                );

                $connection->protocolOnConnect();
                $connection->protocolOnData();
            }
        );

        $this->socketListener->start();
    }

    public function closeConnection(
        SocketListener $server,
        ?int $clientId
    ): void {
        $idConnection = Connection::uniqueConnectionIdFromClientId($server, $clientId);
        if (!empty($this->connections[$idConnection])) {
            $connection = $this->connections[$idConnection];
            $connection->client()->stop();
            unset($this->connections[$idConnection]);
        }
    }

    private function createOnResumeConnection(
        SocketListener $server,
        SocketInterface $client,
        ServerProtocol $protocol
    ): Connection {
        $idConnection = Connection::uniqueConnectionId($server, $client);

        if (empty($this->connections[$idConnection])) {
            $connection = new Connection(
                $server,
                $client,
                $this,
                $protocol
            );
            $this->connections[$idConnection] = $connection;

            return $connection;
        }

        return $this->connections[$idConnection];
    }

    /**
     * @return Connection[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }
}
