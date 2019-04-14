<?php

namespace SocketServer\JsonRpc;

use Exception;
use SocketServer\ServerProtocol;

class JsonRpcProtocol extends ServerProtocol
{
    /**
     * @param JsonRpcRequest $jsonRpcRequest
     * @return false|string
     * @throws Exception
     */
    public function executeCommand($jsonRpcRequest): string
    {
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
