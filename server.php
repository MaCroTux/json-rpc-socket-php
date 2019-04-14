<?php

use JsonRpcServer\JsonRpc;
use JsonRpcServer\JsonRpcRequest;
use JsonRpcServer\Server;
use JsonRpcServer\SubstractAction;
use Kraken\Ipc\Socket\SocketInterface;

require_once 'vendor/autoload.php';

const ADDRESS   = '127.0.0.1';
const PORT      = '2080';

$server = new Server(ADDRESS, PORT);

$jsonRpc = new JsonRpc();

$jsonRpc->addAction(
    SubstractAction::methodName(),
    function($num1, $num2) {
        return (new SubstractAction())->__invoke($num1, $num2);
    }
);

$server->addJsonRpc($jsonRpc);

$server->addOnDataEvent(
    function(SocketInterface $client, $data) use(&$buffer, $jsonRpc) {
        echo $data;
        try {
            $jsonRpcRequest = JsonRpcRequest::buildFromJsonRequest($data);

            $client->write($jsonRpc->executeAction($jsonRpcRequest));
        } catch (\Exception $e) {
            $client->write($e->getMessage());
        }
    }
);

$server->start();
