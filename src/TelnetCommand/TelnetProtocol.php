<?php

namespace SocketServer\TelnetCommand;

use Kraken\Ipc\Socket\SocketInterface;
use SocketServer\Command;
use SocketServer\Server;
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
    /** @var Server */
    private $server;

    public function __construct(Server $server, ?string $password = null)
    {
        if ($password !== null) {
            $this->protect = true;
            $this->password = $password;
        }
        $this->server = $server;
    }

    public function onConnect(SocketInterface $client):void
    {
        $clientId = $client->getResourceId();
        if ($this->protect === true) {
            $client->write(self::PASSWORD.": ");
        } else {
            $client->write(self::WELCOME."(".$clientId.")\n\n$ ");
        }
    }

    /**
     * @param SocketInterface $client
     * @param string $data
     * @return false|string
     */
    public function executeCommand(
        SocketInterface $client,
        string $data
    ): string {
        $command = str_replace(["\n","\r"],['',''], $data);

        if ($this->protect === true && $command !== $this->password) {
            return self::PASSWORD.": ";
        } else if ($this->protect === true && $command === $this->password) {
            $this->protect = false;
            return self::PASSWORD_OK . "\n\n" .
                self::WELCOME . "\n\n$ ";
        }

        if ($command === '\\info') {
            $this->server->info($client);
            return '';
        }

        if ($command === '\\exit') {
            if ($this->password !== null) {
                $this->protect = true;
            }
            $this->server->closeClientConnection($client);
        }

        if ($this->protect === true) {
            return '';
        }

        if (empty($command)) {
            return $this->write('');
        }

        $action = $this->searchCommandCorrect(
            $this->actions,
            $command
        );

        if ($action === null) {
            return $this->write('Not found');
        }

        return $this->write(
            $action->__invoke($client, $command)
        );
    }

    private function searchCommandCorrect(
        array $actions,
        string $command
    ): ?Command {
        foreach ($actions as $action) {
            if ($action->matchData($command)) {
                return $action;
            }
        }

        return null;
    }

    private function write(string $line): string
    {
        return $line."\n"."$ ";
    }
}