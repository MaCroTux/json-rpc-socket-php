<?php

namespace SocketServer;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;

abstract class ServerProtocol
{
    /** @var array */
    protected $actions;
    /** @var ServerProtocol[] */
    protected $clientConnection;

    public function addCommand(Command $command)
    {
        $this->actions[$command->methodName()] = $command;
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

    protected function info(Connection $connection)
    {
        $client = $connection->client();
        $client
            ->getLoop()
            ->onTick(function() use ($client) {
                $info = sprintf("%s\n", str_repeat('-', 42));
                $info .= sprintf("%s\n", 'Client info:');
                $info .= sprintf("%s\n", str_repeat('-', 42));
                $info .= sprintf("%-20s%s\n", 'Resource ID:', '#' . $client->getResourceId());
                $info .= sprintf("%-20s%s\n", 'Local endpoint:', $client->getLocalEndpoint());
                $info .= sprintf("%-20s%s\n", 'Local protocol:', $client->getLocalProtocol());
                $info .= sprintf("%-20s%s\n", 'Local address:', $client->getLocalAddress());
                $info .= sprintf("%-20s%s\n", 'Local host:', $client->getLocalHost());
                $info .= sprintf("%-20s%s\n", 'Local port:', $client->getLocalPort());
                $info .= sprintf("%-20s%s\n", 'Remote endpoint:', $client->getRemoteEndpoint());
                $info .= sprintf("%-20s%s\n", 'Remote protocol:', $client->getRemoteProtocol());
                $info .= sprintf("%-20s%s\n", 'Remote address:', $client->getRemoteAddress());
                $info .= sprintf("%-20s%s\n", 'Remote host:', $client->getRemoteHost());
                $info .= sprintf("%-20s%s\n", 'Remote port:', $client->getRemotePort());
                $info .= sprintf("%s\n", str_repeat('-', 42));

                $client->write($info);
            });
    }

    protected function usersInfo(array $connections)
    {
        $numClients = count($connections);
        $info = '';
        $info .= sprintf("\n%-20s%s\n", 'Client connect:', $numClients);

        $info .= sprintf(
            "%-10s%-10s%-10s %s\n",
            'Connect',
            'Client id',
            'Port',
            'Date'
        );

        $info .= sprintf(
            "%-10s%-10s%-10s %s\n",
            str_repeat('-',9),
            str_repeat('-',9),
            str_repeat('-',9),
            str_repeat('-',18)
        );

        /** @var ServerProtocol $protocol */
        foreach ($connections as $connection) {
            $info .= sprintf(
                "%-10s%-10s%-10s %s\n",
                $connection['isOpen'] === true ? 'Open' : 'Closed',
                $connection['clientId'],
                $connection['port'],
                $connection['date']
            );
        }

        return $info;
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
