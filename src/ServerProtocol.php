<?php

namespace SocketServer;

use Kraken\Ipc\Socket\SocketInterface;

abstract class ServerProtocol
{
    /** @var array */
    protected $actions;
    /** @var SocketInterface */
    protected $client;

    public function addCommand(Command $command)
    {
        $this->actions[$command->methodName()] = $command;
    }

    public function client(): array
    {
        return [
            'isOpen'   => $this->client->isOpen(),
            'clientId' => $this->client->getResourceId(),
            'port'     => $this->client->getLocalPort(),
            'client'   => $this->client,
        ];
    }

    /**
     * @param SocketInterface $client
     */
    abstract public function onConnect(
        SocketInterface $client
    ):void;

    /**
     * @param SocketInterface $client
     * @param string $data
     * @return false|string
     */
    abstract public function executeCommand(
        SocketInterface $client,
        string $data
    ): string;
}