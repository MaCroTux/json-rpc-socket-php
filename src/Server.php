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
    private $clientConnection = [];

    public function __construct(SocketListener $server)
    {
		$this->server = $server;
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
                $this->server = $server;
                $this->protocol->onConnect($client);
				$this->onDataEvent($client);
            }
        );
	}

	private function onDataEvent(SocketInterface $client): void
	{
        $client->on(
            'data',
            function(SocketInterface $client, $data) use(&$buffer) {
                $clientId = $client->getResourceId();

                $this->logData($client, $data);

                try {
                    $session = $this->startSession($clientId);

                    $client->write(
                        $session->executeCommand($client, $data)
                    );
                } catch (\Exception $e) {
                    $client->write($e->getMessage());
                }
		    }
        );
	}

    /**
     * @param $clientId
     * @return ServerProtocol
     */
    private function startSession($clientId): ServerProtocol
    {
        if (empty($this->clientConnection[$clientId])) {
            echo "\nCreate new session client Id #" . $clientId . "\n";
            $this->clientConnection[$clientId] = clone $this->protocol;

            return $this->clientConnection[$clientId];
        }

        return $this->clientConnection[$clientId];
    }

    public function setProtocol(ServerProtocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function deleteSession(int $getResourceId): void
    {
        unset($this->clientConnection[$getResourceId]);
    }

    public function info($client)
    {
        printf("%s\n", str_repeat('-', 42));
        printf("%s\n", 'Client info:');
        printf("%s\n", str_repeat('-', 42));
        printf("%-20s%s\n", 'Resource ID:', '#' . $client->getResourceId());
        printf("%-20s%s\n", 'Local endpoint:', $client->getLocalEndpoint());
        printf("%-20s%s\n", 'Local protocol:', $client->getLocalProtocol());
        printf("%-20s%s\n", 'Local address:', $client->getLocalAddress());
        printf("%-20s%s\n", 'Local host:', $client->getLocalHost());
        printf("%-20s%s\n", 'Local port:', $client->getLocalPort());
        printf("%-20s%s\n", 'Remote endpoint:', $client->getRemoteEndpoint());
        printf("%-20s%s\n", 'Remote protocol:', $client->getRemoteProtocol());
        printf("%-20s%s\n", 'Remote address:', $client->getRemoteAddress());
        printf("%-20s%s\n", 'Remote host:', $client->getRemoteHost());
        printf("%-20s%s\n", 'Remote port:', $client->getRemotePort());
        printf("%s\n", str_repeat('-', 42));
    }

    /**
     * @param SocketInterface $client
     * @param $data
     */
    private function logData(SocketInterface $client, $data):void
    {
        $date     = date('d-m-y H:i:s', time());
        $ip       = $client->getRemoteAddress();
        $clientId = $client->getResourceId();
        $data = trim($data);

        if (!mb_detect_encoding(trim($data), 'ASCII')) {
            $data = bin2hex($data);
        }

        $log = "[{$date}/{$ip}/#{$clientId}]: {$data}\n";
        file_put_contents('server.log',$log, FILE_APPEND);

        echo $log;
    }
}
