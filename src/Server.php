<?php

namespace SocketServer;

use Closure;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;

class Server
{
    /** @var SocketListener */
    private $server;
    /** @var ServerProtocol */
    private $protocol;
    /** @var null|string */
    private $welcome;

    public function __construct(SocketListener $socketListener, ?string $welcome = null)
    {
        $this->welcome = $welcome;
        $this->server = $socketListener;
    }

    public function server(): SocketListener
    {
        return $this->server;
    }

    public function addOnDataEvent(Closure $onData)
    {
        $this->server->on(
            'connect',
            function(SocketListener $server, SocketInterface $client) use ($onData) {

                if ($this->welcome) {
                    $client->write($this->welcome);
                }

                $client->on('data', $onData);
            }
        );
    }

    public function addProtocol(ServerProtocol $protocol)
    {
        $this->protocol = $protocol;
    }
}
