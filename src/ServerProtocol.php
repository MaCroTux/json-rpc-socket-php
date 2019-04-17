<?php

namespace SocketServer;

use Kraken\Ipc\Socket\SocketInterface;

abstract class ServerProtocol
{
    /** @var array */
    protected $actions;

    public function addCommand(Command $command)
    {
        $this->actions[$command->methodName()] = $command;
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