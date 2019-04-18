<?php

use SocketServer\Config;
use SocketServer\Socket;
use SocketServer\JsonRpc\JsonRpcProtocol;
use SocketServer\JsonRpc\SumCommand;
use SocketServer\TelnetCommand\BashCommand;
use SocketServer\TelnetCommand\TelnetProtocol;

require_once 'vendor/autoload.php';

const ADDRESS     = '0.0.0.0';
const PORT_JSON   = '2080';
const PORT_TELNET = '2081';
const TELNET_CONF = 'telnet_config.json';

$socket    = new Socket();

// --------------------- JSON-RPC

$jsonRpcProtocol = new JsonRpcProtocol();
$jsonRpcProtocol->addCommand(new SumCommand());

$serverRpc = $socket->createServer(ADDRESS, PORT_JSON);

$serverRpc->setProtocol($jsonRpcProtocol);

// --------------------- TELNET COMMAND

$serverTelnet = $socket->createServer(ADDRESS, PORT_TELNET);

$telnetProtocol = new TelnetProtocol(
    $serverTelnet,
    new Config(TELNET_CONF)
);

$telnetProtocol->addCommand(new BashCommand('bash_alias_command.json'));

$serverTelnet->setProtocol($telnetProtocol);

// --------------------- SERVER INIT

$socket->addServers($serverRpc);
$socket->addServers($serverTelnet);

echo "* JSON-RPC server running on 2080, you execute for example 'nc 127.0.0.1 2080 < res.json'\n";
echo "* TELNET server running on 2081, you execute for example 'nc 127.0.0.1 2081' and type 'ls'\n\n\n";

$socket->start();
