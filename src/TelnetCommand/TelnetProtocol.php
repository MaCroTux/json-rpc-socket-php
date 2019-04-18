<?php

namespace SocketServer\TelnetCommand;

use Kraken\Ipc\Socket\SocketInterface;
use SocketServer\Command;
use SocketServer\Config;
use SocketServer\Server;
use SocketServer\ServerProtocol;

class TelnetProtocol extends ServerProtocol
{
    private const PASSWORD = "Password";
    private const PASSWORD_OK = "Password correct";

    /** @var string */
    private $welcome;
    /** @var bool */
    private $protect = false;
    /** @var string */
    private $password;
    /** @var Server */
    private $server;
    /** @var Config */
    private $config;
    /** @var string */
    private $prompt;

    public function __construct(Server $server, Config $config)
    {
        $this->server = $server;
        $this->config = $config;
        $this->loadConfigFile();
    }

    private function loadConfigFile(): void
    {
        $this->welcome = $this->config->get('telnetWelcome');
        $password      = $this->config->get('password');

        if ($password !== null) {
            $this->protect = true;
            $this->password = $password;
        }
    }

    public function onConnect(SocketInterface $client):void
    {
        $this->loadConfigFile();
        $this->client = $client;
        $clientId     = $client->getResourceId();
        $host         = gethostname();
        $this->prompt = "{$clientId}@{$host}:~$ ";

        if ($this->protect === true) {
            $client->write(self::PASSWORD.": ");
        } else {
            $client->write($this->welcome."\n\n".$this->prompt);
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
                $this->welcome . "\n\n" . $this->prompt;
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

        if ($command === '\\users') {
            $this->server->usersInfo($client);
            return '';
        }

        if (strpos($command, '\\kick') === 0) {
            list($command, $clientId) = explode(' ', $command);
            $this->server->closeConnect($clientId);
            $this->client->write("User {$clientId} kicked");
            return '';
        }

        if (strpos($command, '\\change_pass') === 0) {
            $commandList = explode(' ', $command);
            $oldPass = $commandList[1];
            $newPass = $commandList[2] ?? null;
            if ($this->password === null) {
                $this->password = $oldPass;
            } else if ($this->password === $oldPass) {
                $this->password = $newPass;
            }

            $this->config->setOrOverwrite('password', $this->password);
            $this->loadConfigFile();

            $this->client->write("Password change");
            return '';
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
        return $line."\n".$this->prompt;
    }
}