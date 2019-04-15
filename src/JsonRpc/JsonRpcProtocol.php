<?php

namespace SocketServer\JsonRpc;

use Exception;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use SocketServer\ServerProtocol;

class JsonRpcProtocol extends ServerProtocol
{
    public function onConnect(SocketListener $server, SocketInterface $client):void
    {

    }

    /**
     * @param string $data
     * @return false|string
     * @throws Exception
     */
    public function executeCommand(string $data): string
    {
        $jsonRpcRequest = JsonRpcRequest::buildFromRequest($data);
        $params = $jsonRpcRequest->params();

        $this->validateMethod($jsonRpcRequest);
        $command = $this->actions[$jsonRpcRequest->method()];

        return json_encode([
            'jsonrpc' => '2.0',
            'id' => $jsonRpcRequest->id(),
            'result' => (new $command())->__invoke(... $params)
        ]);
    }

    /**
     * @param JsonRpcRequest $jsonRpcRequest
     * @throws Exception
     */
    private function validateMethod(JsonRpcRequest $jsonRpcRequest)
    {
        if (empty($jsonRpcRequest->id())) {
            throw new \Exception(
                json_encode([
                    'jsonrpc' => $jsonRpcRequest->version(),
                    'id' => uniqid(),
                    'result' => null,
                    'error' => 'Id not found'
                ])
            );
        }

        if (empty($this->actions[$jsonRpcRequest->method()])) {
            throw new \Exception(
                json_encode([
                    'jsonrpc' => $jsonRpcRequest->version(),
                    'id' => $jsonRpcRequest->id(),
                    'result' => null,
                    'error' => 'Method not found'
                ])
            );
        }
    }
}
