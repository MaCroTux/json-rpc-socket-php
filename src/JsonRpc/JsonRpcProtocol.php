<?php

namespace SocketServer\JsonRpc;

use Exception;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use SocketServer\Connection;
use SocketServer\ServerProtocol;

class JsonRpcProtocol extends ServerProtocol
{
    public function onConnect(SocketListener $server, SocketInterface $client):void
    {
    }

    public function onData(Connection $connection, string $query): void
    {
        $client = $connection->client();
        try {
            $response = $this->executeCommand($connection, $query);

            $client
                ->getLoop()
                ->onTick(function() use ($client, $query, $response) {
                    $this->logData($client, $query, $response);
                });

            $client->write($response);
        } catch (\Exception $e) {
            $client->write($e->getMessage());
        }
    }

    /**
     * @param Connection $connection
     * @param string $data
     * @return false|string
     * @throws Exception
     */
    public function executeCommand(
        Connection $connection,
        string $data
    ): string {
        $jsonRpcRequest = JsonRpcRequest::buildFromRequest($data);

        $this->validateMethod($jsonRpcRequest);
        $rpcClass = $this->rpcCommands[$jsonRpcRequest->method()];

        /** @var RpcCommand $rpcCommand */
        $rpcCommand = new $rpcClass();
        $result = $rpcCommand->rpcJson($connection, $jsonRpcRequest);

        return json_encode([
            'jsonrpc' => '2.0',
            'id' => $jsonRpcRequest->id(),
            'result' => $result,
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

        if (empty($this->rpcCommands[$jsonRpcRequest->method()])) {
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
