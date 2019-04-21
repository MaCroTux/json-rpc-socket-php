<?php

namespace SocketServer;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;

abstract class ServerProtocol
{
    /** @var array */
    protected $rpcCommands;
    /** @var ServerProtocol[] */
    protected $clientConnection;

    public function addCommand(Command $command)
    {
        $this->rpcCommands[$command->methodName()] = $command;
    }

    protected function logData(SocketInterface $client, string $query, string $response):void
    {
        $date     = date('d-m-y:H:i:s', time());
        $ip       = $client->getRemoteAddress();
        $clientId = $client->getResourceId();
        $remoteIp = $client->getLocalAddress();
        $query    = $this->cleanString($query);
        $response = $this->cleanString($response);

        if (!mb_detect_encoding(trim($query), 'ASCII')) {
            $query = bin2hex($query);
        }

        $log = "[{$date}/{$ip}>{$remoteIp}/#{$clientId}]: [{$query}]>[{$response}]\n";
        file_put_contents('server.log',$log, FILE_APPEND);

        echo $log;
    }

    protected function cleanString(string $data): string
    {
        return trim(str_replace(["\n","\r"],['',''], $data));
    }

    /**
     * @param SocketListener $server
     * @param SocketInterface $client
     */
    abstract public function onConnect(
        SocketListener $server,
        SocketInterface $client
    ):void;

    abstract public function onData(
        Connection $connection,
        string $query
    ): void;

    /**
     * @param Connection $connection
     * @param string $data
     * @return false|string
     */
    abstract public function executeCommand(
        Connection $connection,
        string $data
    ): string;
}
