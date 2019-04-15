<?php

namespace SocketServer;

use Kraken\Ipc\Socket\SocketListener;
use Kraken\Loop\Loop;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Throwable\Exception\Logic\InstantiationException;

class Socket
{
    private const PROTOCOL  = 'tcp';

    /** @var Loop  */
    private $loop;

    public function __construct()
    {
        $this->loop = new Loop(new SelectLoop);
    }

    public function addServers(Server $server)
    {
        $this->loop->onStart(function() use($server) {
            $server->server()->start();
        });
    }

    public function createServer(
        string $address,
        string $port
    ): Server {
        try {
            $socketListener = new SocketListener(
                self::PROTOCOL . '://' . $address . ':' . $port,
                $this->loop
            );

            return new Server($socketListener);
        } catch (InstantiationException $e) {
            die($e->getMessage());
        }
    }

    public function start()
    {
        $this->loop->start();
    }
}
