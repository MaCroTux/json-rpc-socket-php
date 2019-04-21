<?php

namespace Custom;

use Kraken\Ipc\Socket\SocketInterface;
use SocketServer\Command;

class BashCommand implements Command
{
    /** @var array */
    private $commands;
    /** @var string */
    private $commandFiles;

    public function __construct(string $commandFiles)
    {
        $this->commandFiles = $commandFiles;
        $this->loadCommandFile($commandFiles);
    }

    private function loadCommandFile($commandFiles): void
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

        if ($command === 'reload') {
            $this->loadCommandFile($this->commandFiles);
            return "Reload config file";
        }

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

    public function description(): string
    {
        return 'Bash command utilities, type .h';
    }
}