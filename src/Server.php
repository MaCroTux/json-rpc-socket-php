<?php

namespace SocketServer;

use Closure;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use WebSocket\Client;

class Server
{
    /** @var SocketListener */
    private $server;
    /** @var ServerProtocol */
    private $protocol;

    public function __construct(SocketListener $socketListener)
    {
		$this->server = $socketListener;
    }

    public function server(): SocketListener
    {
        $this->onConnectEvent();
        return $this->server;
    }

    public function onConnectEvent()
    {
        $this->server->on(
            'connect',
            function(SocketListener $server, SocketInterface $client) {
                $this->protocol->onConnect($server, $client);
				$this->onDataEvent($client);
            }
        );
	}

	private function onDataEvent(SocketInterface $client): void
	{
        $client->on(
            'data',
            function(SocketInterface $client, $data) use(&$buffer) {
                echo "[".date(DATE_ISO8601, time())."]: ".$data."\n";
                try {
                    $client->write(
                        $this->protocol->executeCommand($data)
                    );
                } catch (\Exception $e) {
                    $client->write($e->getMessage());
                }
		    }
        );
	}

    public function addProtocol(ServerProtocol $protocol)
    {
        $this->protocol = $protocol;
    }
}
