<?php

namespace JsonRpcServer;

use Exception;

class JsonRpc
{
    /** @var array */
    private $actions;

    public function addAction(string $method, callable $class)
    {
        $this->actions[$method] = $class;
    }

    /**
     * @param JsonRpcRequest $jsonRpcRequest
     * @return false|string
     * @throws Exception
     */
    public function executeAction(JsonRpcRequest $jsonRpcRequest)
    {
        $params = $jsonRpcRequest->params();

        $this->validateMethod($jsonRpcRequest);

        return json_encode([
            'jsonrpc' => '2.0',
            'id' => $jsonRpcRequest->id(),
            'result' => $this->actions[$jsonRpcRequest->method()](... $params)
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
