<?php

namespace SocketServer;

interface Command
{
    public function methodName(): string;
}