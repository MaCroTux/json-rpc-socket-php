<?php

namespace SocketServer\JsonRpc;

use SocketServer\Connection;
use SocketServer\Command;

interface RpcCommand extends Command
{
    public function methodName(): string;

    public function terminalMatchCommand(string $command): bool;

    public function rpcJson(
        Connection $connection,
        JsonRpcRequest $jsonRpcRequest
    ): string;

    public function terminal(Connection $connection, string $query): string;
}
