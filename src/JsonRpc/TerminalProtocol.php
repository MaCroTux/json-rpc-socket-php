<?php

namespace SocketServer\JsonRpc;

use Kraken\Ipc\Socket\SocketInterface;
use Kraken\Ipc\Socket\SocketListener;
use SocketServer\Config;
use SocketServer\Connection;
use SocketServer\ServerProtocol;
use SocketServer\TerminalTrait;

class TerminalProtocol extends ServerProtocol
{
    use TerminalTrait;

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
        $client  = $connection->client();
        $cleanQuery = $this->cleanString($query);

        try {
            if ($this->protect === true && $cleanQuery !== $this->password) {
                $client->write(self::PASSWORD.": ");

                return;
            } else if ($this->protect === true && $cleanQuery === $this->password) {
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
                $cleanQuery
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
     * @param string $query
     * @return false|string
     */
    public function executeCommand(
        Connection $connection,
        string $query
    ): string {
        if ($this->protect === true) {
            return '';
        }

        if (empty($query)) {
            return '';
        }

        $result = $this->terminalCommandExtend(
            $connection,
            $query,
            $this->config
        );

        if ($result !== null) {
            return $result;
        }

        $rpcCommand = $this->searchCommandCorrect(
            $this->rpcCommands,
            $query
        );

        if ($rpcCommand === null) {
            return 'Not found';
        }

        return $rpcCommand->terminal($connection, $query);
    }

    /**
     * @param RpcCommand[] $rpcCommands
     * @param string $query
     * @return null|RpcCommand
     */
    private function searchCommandCorrect(
        array $rpcCommands,
        string $query
    ): ?RpcCommand {
        foreach ($rpcCommands as $rpcCommand) {
            if ($rpcCommand->terminalMatchCommand($query)) {
                return $rpcCommand;
            }
        }

        return null;
    }

    private function write(string $line, SocketInterface $client): void
    {
        $client->write($line."\n".$this->prompt);
    }
}
