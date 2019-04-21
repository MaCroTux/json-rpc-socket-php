<?php

namespace SocketServer;

use SocketServer\JsonRpc\JsonRpcProtocol;
use SocketServer\JsonRpc\TerminalProtocol;

class ServerJsonRpc extends Server
{
    private const TELNET_CONF = 'telnet_config.json';

    /**
     * @param int $rpcPort
     * @param array $commands
     * @param int|null $terminalPort
     * @return ServerJsonRpc
     * @throws \Exception
     */
    public static function buildServerJsonRpc(
        int $rpcPort,
        array $commands,
        ?int $terminalPort
    ): Server {
        $jsonRpcProtocol = new JsonRpcProtocol();
        self::loadCommands($jsonRpcProtocol, $commands);

        $server = self::buildCustomServerFromProtocolAndPort(
            $jsonRpcProtocol,
            $rpcPort
        );

        if ($terminalPort !== null) {
            $terminalConfig = new Config(self::TELNET_CONF);
            $terminalProtocol = new TerminalProtocol($terminalConfig);
            self::loadCommands($terminalProtocol, $commands);
            $server->addCustomServerWithProtocolAndPort($terminalProtocol, $terminalPort);
        }

        return $server;
    }

    private static function loadCommands(ServerProtocol $protocol, array $commands): void
    {
        foreach ($commands as $command) {
            $protocol->addCommand($command);
        }
    }
}