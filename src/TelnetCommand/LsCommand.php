<?php

namespace SocketServer\TelnetCommand;

use SocketServer\Command;

class LsCommand implements Command
{
    public function __invoke()
    {
        return exec('ls');
    }

    public function methodName(): string
    {
        return 'ls';
    }
}