<?php

namespace Example;

use SocketServer\Connection;
use SocketServer\JsonRpc\JsonRpcRequest;
use SocketServer\JsonRpc\RpcCommand;

class SumCommand implements RpcCommand
{
    public function methodName(): string
    {
        return 'sum';
    }

    public function description(): string
    {
        return 'Sum two numbers and return result.';
    }

    public function terminalMatchCommand(string $command): bool
    {
        return strpos($command, $this->methodName()) === 0;
    }

    public function rpcJson(
        Connection $connection,
        JsonRpcRequest $jsonRpcRequest
    ): string {
        var_dump($jsonRpcRequest->params());
        $num1 = $jsonRpcRequest->params()[0] ?? 0;
        $num2 = $jsonRpcRequest->params()[1] ?? 0;

        return (string)($num1 + $num2);
    }

    public function terminal(Connection $connection, string $query): string
    {
        list($command, $num1, $num2) = explode(' ', $query);

        return (string)($num1 + $num2);
    }
}
