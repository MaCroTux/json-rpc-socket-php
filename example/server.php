<?php

use Custom\BashCommand;
use Custom\TelnetProtocol;
use Example\SumCommand;
use SocketServer\Config;
use SocketServer\ServerJsonRpc;

require_once 'vendor/autoload.php';

const PORT_JSON     = 2080;
const PORT_TERMINAL = 2081;

// --------------------- SERVER RPC JSON AND TERMINAL SUPPORT

try {
    $server = ServerJsonRpc::buildServerJsonRpc(
        PORT_JSON,
        [
            new SumCommand()
        ],
        PORT_TERMINAL
    );
} catch (Exception $e) {
    die($e->getMessage());
}

// --------------------- CUSTOM SERVER TELNET

const TERMINAL_CONF = 'terminal_config.json';

$telnetProtocol = new TelnetProtocol(new Config(TERMINAL_CONF));
$telnetProtocol->addCommand(new BashCommand('bash_alias_command.json'));

try {
    $server->addCustomServerWithProtocolAndPort($telnetProtocol, 2024);
} catch (Exception $e) {
    die($e->getMessage());
}

echo "* JSON-RPC server running on 2080, you execute for example 'nc 127.0.0.1 2080 < res.json'\n";
echo "* TERMINAL server running on 2081, you execute for example 'nc 127.0.0.1 2081' and type 'ls'\n\n\n";
echo "* CUSTOM TELNET server running on 2024, you execute for example 'nc 127.0.0.1 2024' and type 'sum 1 2'\n\n\n";

$server->start();
