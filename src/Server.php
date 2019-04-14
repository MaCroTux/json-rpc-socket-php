<?php

namespace JsonRpcServer;

use Closure;
use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use Kraken\Loop\Loop;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Throwable\Exception\Logic\InstantiationException;

class Server
{
    private const PROTOCOL  = 'tcp';
    /** @var SocketListener */
    private $server;
    /** @var Loop */
    private $loop;
    /** @var JsonRpc */
    private $jsonRpc;

    public function __construct(string $address, string $port)
    {
        $this->loop = new Loop(new SelectLoop);

        try {
            $this->server = new SocketListener(
                self::PROTOCOL . '://' . $address . ':'  .$port,
                $this->loop
            );
        } catch (InstantiationException $e) {
            die($e->getMessage());
        }
    }

    public function addOnDataEvent(Closure $onData)
    {
        $this->server->on(
            'connect',
            function(SocketListener $server, SocketInterface $client) use ($onData) {
                $jsonRpm = $this->jsonRpc;
                $client->on('data', $onData);
            }
        );
    }

    public function start()
    {
        $server = $this->server;

        $this->loop->onStart(function() use($server) {
            $server->start();
        });

        $this->loop->start();
    }

    public function addJsonRpc(JsonRpc $jsonRpc)
    {
        $this->jsonRpc = $jsonRpc;
    }
}
