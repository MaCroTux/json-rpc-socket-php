<?php

namespace SocketServer;

interface Command
{
    public function methodName(): string;

    public function description(): string;
}
