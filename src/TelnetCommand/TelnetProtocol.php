<?php

namespace SocketServer\TelnetCommand;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use SocketServer\Command;
use SocketServer\Config;
use SocketServer\Connection;
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
    /** @var Config */
    private $config;
    /** @var string */
    private $prompt;

    public function __construct(Config $config)
    {
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

    public function onConnect(SocketListener $server, SocketInterface $client): void
    {
        $this->loadConfigFile();

        $port         = $server->getLocalPort();
        $clientId     = $client->getResourceId();
        $host         = gethostname();
        $this->prompt = "{$clientId}@{$host}:~$ ";

        if ($this->protect === true) {
            $client->write(self::PASSWORD.": ");
        } else {
            $client->write(
                $this->welcome .
                ' [' . $port . '] ' .
                "\n\n" . $this->prompt
            );
        }
    }

    public function onData(
        Connection $connection,
        string $query
    ): void {
        var_dump('#'.$connection->id());
        $client  = $connection->client();
        $command = $this->cleanString($query);

        try {
            if ($this->protect === true && $command !== $this->password) {
                $client->write(self::PASSWORD.": ");

                return;
            } else if ($this->protect === true && $command === $this->password) {
                $this->protect = false;
                $client->write(
                    self::PASSWORD_OK .
                        "\n\n" .
                        $this->welcome .
                        "\n\n" .
                        $this->prompt
                );

                return;
            }

            $response = $this->executeCommand(
                $connection,
                $command
            );

            $client
                ->getLoop()
                ->onTick(function() use ($client, $query, $response) {
                    $this->logData($client, $query, $response);
                });

            $this->write($response, $client);
        } catch (\Exception $e) {
            $this->write($e->getMessage(), $client);
        }
    }

    /**
     * @param Connection $connection
     * @param string $command
     * @return false|string
     */
    public function executeCommand(
        Connection $connection,
        string $command
    ): string {
        $client  = $connection->client();

        if ($command === '\\info') {
            $this->info($connection);
            return '';
        }

        if ($command === '\\exit') {
            if ($this->password !== null) {
                $this->protect = true;
            }
            $connection->closeConnect();
            return 'Closed session';
        }

        if ($command === '\\users') {
            return $this->usersInfo($connection->getAllConnections());
        }

        if (strpos($command, '\\kick') === 0) {
            list($command, $clientId) = explode(' ', $command);
            $connection->kickConnect($clientId);
            return "User {$clientId} kicked";
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

            return "Password change";
        }

        if ($this->protect === true) {
            return '';
        }

        if (empty($command)) {
            return '';
        }

        $action = $this->searchCommandCorrect(
            $this->actions,
            $command
        );

        if ($action === null) {
            return 'Not found';
        }

        return $action->__invoke($client, $command);
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

    private function write(string $line, SocketInterface $client): void
    {
        $client->write($line."\n".$this->prompt);
    }
}