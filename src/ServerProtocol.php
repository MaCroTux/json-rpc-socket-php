<?php

namespace SocketServer;

abstract class ServerProtocol
{
    /** @var array */
    protected $actions;

    public function addCommand(Command $command)
    {
        $this->actions[$command->methodName()] = $command;
    }

    /**
     * @param $request
     * @return false|string
     */
    abstract public function executeCommand($request): string;
}