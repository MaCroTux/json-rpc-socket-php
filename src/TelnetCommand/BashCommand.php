<?php

namespace SocketServer\TelnetCommand;

use Kraken\Ipc\Socket\SocketInterface;
use SocketServer\Command;

class BashCommand implements Command
{
    /** @var array */
    private $commands;

    public function __construct(string $commandFiles)
    {
        $dataFile = file_get_contents($commandFiles);
        $this->commands = json_decode($dataFile, true);
    }

    public function matchData(string $data): bool
    {
        return strpos($data, '.') === 0;
    }

    public function __invoke(
        SocketInterface $client,
        string $data
    ): string {
        $data    = substr($data, 1);
        $args    = explode(' ', $data);
        $command = array_shift($args);

        if (empty($this->commands[$command])) {
            return 'Command not found';
        }

        $exec = $this->commands[$command];

        return shell_exec($exec . ' ' . implode(' ', $args));
    }

    public function methodName(): string
    {
        return '.';
    }
}