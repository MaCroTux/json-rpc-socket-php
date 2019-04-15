<?php

use SocketServer\Socket;
use SocketServer\JsonRpc\JsonRpcProtocol;
use SocketServer\JsonRpc\SumCommand;
use SocketServer\TelnetCommand\LsCommand;
use SocketServer\TelnetCommand\TelnetProtocol;

require_once 'vendor/autoload.php';

const ADDRESS     = '0.0.0.0';
const PORT_JSON   = '2080';
const PORT_TELNET = '2081';

$socket    = new Socket();

// --------------------- JSON-RPC

$jsonRpcProtocol = new JsonRpcProtocol();
$jsonRpcProtocol->addCommand(new SumCommand());

$serverRpc = $socket->createServer(ADDRESS, PORT_JSON);

$serverRpc->addProtocol($jsonRpcProtocol);

// --------------------- TELNET COMMAND

$telnetProtocol = new TelnetProtocol('pass');
$telnetProtocol->addCommand(new LsCommand());

$serverTelnet = $socket->createServer(ADDRESS, PORT_TELNET);

$serverTelnet->addProtocol($telnetProtocol);

// --------------------- SERVER INIT

$socket->addServers($serverRpc);
$socket->addServers($serverTelnet);

echo "* JSON-RPC server running on 2080, you execute for example 'nc 127.0.0.1 2080 < res.json'\n";
echo "* TELNET server running on 2081, you execute for example 'nc 127.0.0.1 2081' and type 'ls'\n";

$socket->start();
