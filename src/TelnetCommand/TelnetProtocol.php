<?php

namespace SocketServer\TelnetCommand;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use SocketServer\ServerProtocol;

class TelnetProtocol extends ServerProtocol
{
    private const WELCOME = "Welcome to MaCroServer!";
    private const PASSWORD = "Password";
    private const PASSWORD_OK = "Password correct";

    /** @var bool */
    private $protect = false;
    /** @var string */
    private $password;

    public function __construct(?string $password = null)
    {
        if ($password !== null) {
            $this->protect = true;
            $this->password = $password;
        }
    }

    public function onConnect(SocketListener $server, SocketInterface $client):void
    {
        if ($this->protect === true) {
            $client->write(self::PASSWORD.": ");
        } else {
            $client->write(self::WELCOME."\n\n$ ");
        }
    }

    /**
     * @param string $data
     * @return false|string
     */
    public function executeCommand(string $data): string
    {
        $command = str_replace(["\n","\r"],['',''], $data);

        if ($this->protect === true && $command !== $this->password) {
            return self::PASSWORD.": ";
        } else if ($this->protect === true && $command === $this->password) {
            $this->protect = false;
            return self::PASSWORD_OK . "\n\n" . self::WELCOME . "\n\n$ ";
        }

        if ($this->protect === true) {
            return '';
        }

        if (empty($command)) {
            return $this->write('');
        }

        if (empty($this->actions[$command])) {
            return $this->write('Not found');
        }

        $command = $this->actions[$command];

        return $this->write($command->__invoke());
    }

    private function write(string $line): string
    {
        return $line."\n"."$ ";
    }
}