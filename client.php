<?php

use Kraken\Ipc\Socket\Socket;
use Kraken\Loop\Loop;
use Kraken\Loop\Model\SelectLoop;

require_once 'vendor/autoload.php';

$loop = new Loop(new SelectLoop);
try {
    $socket = new Socket('tcp://127.0.0.1:2080', $loop);
} catch (\Kraken\Throwable\Exception\Logic\InstantiationException $e) {
    die($e->getMessage());
}

$socket->on('data', function(Socket $socket, $data) {
    echo $data;
});

$socket->write(
    '{"jsonrpc":"2.0","method":"substract","id":"31df19bc-c0bf-465a-af30-39bfe483730b","params":[51,9]}'
);

$loop->start();