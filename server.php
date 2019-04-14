<?php

use SocketServer\Socket;
use Kraken\Ipc\Socket\SocketInterface;
use SocketServer\JsonRpc\JsonRpcProtocol;
use SocketServer\JsonRpc\JsonRpcRequest;
use SocketServer\JsonRpc\SumCommand;
use SocketServer\TelnetCommand\LsCommand;
use SocketServer\TelnetCommand\TelnetProtocol;

require_once 'vendor/autoload.php';

const ADDRESS     = '127.0.0.1';
const PORT_JSON   = '2080';
const PORT_TELNET = '2081';

$socket    = new Socket();

// --------------------- JSON-RPC

$serverRpc = $socket->createServer(ADDRESS, PORT_JSON);

$jsonRpc = new JsonRpcProtocol();
$jsonRpc->addCommand(new SumCommand());

$serverRpc->addProtocol($jsonRpc);

$serverRpc->addOnDataEvent(
    function(SocketInterface $client, $data) use(&$buffer, $jsonRpc) {
        echo "[".date(DATE_ISO8601, time())."]: ".$data."\n";
        try {
            $jsonRpcRequest = JsonRpcRequest::buildFromRequest($data);

            $client->write($jsonRpc->executeCommand($jsonRpcRequest));
        } catch (\Exception $e) {
            $client->write($e->getMessage());
        }
    }
);

// --------------------- TELNET COMMAND

$telnetProtocol = new TelnetProtocol();
$telnetProtocol->addCommand(new LsCommand());

$serverTelnet = $socket->createServer(ADDRESS, PORT_TELNET, "Welcome to MaCroServer!\n\n$ ");

$serverTelnet->addOnDataEvent(
    function(SocketInterface $client, $data) use(&$buffer, $telnetProtocol) {
        echo "[".date(DATE_ISO8601, time())."]: ".$data."\n";
        try {
            $client->write($telnetProtocol->executeCommand($data));
        } catch (\Exception $e) {
            $client->write($e->getMessage());
        }
    }
);

// --------------------- SERVER INIT

$socket->addServers($serverRpc);
$socket->addServers($serverTelnet);

echo "* JSON-RPC server running on 2080, you execute for example 'nc 127.0.0.1 2080 < res.json'\n";
echo "* TELNET server running on 2081, you execute for example 'nc 127.0.0.1 2081' and type 'ls'\n";

$socket->start();