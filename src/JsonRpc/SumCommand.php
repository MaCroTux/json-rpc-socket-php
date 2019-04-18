<?php

namespace SocketServer\JsonRpc;

use Kraken\Ipc\Socket\SocketInterface;
use SocketServer\Command;

class SumCommand implements Command
{
    public function methodName(): string
    {
        return 'sum';
    }

    public function __invoke(
        SocketInterface $client,
        $num1,
        $num2
    ): string {
        return (string)($num1 + $num2);
    }
}
