<?php

namespace SocketServer;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;

abstract class ServerProtocol
{
    /** @var array */
    protected $actions;

    public function addCommand(Command $command)
    {
        $this->actions[$command->methodName()] = $command;
    }

    /**
     * @param SocketListener $server
     * @param SocketInterface $client
     */
    abstract public function onConnect(
        SocketListener $server,
        SocketInterface $client
    ):void;

    /**
     * @param $data
     * @return false|string
     */
    abstract public function executeCommand(string $data): string;
}