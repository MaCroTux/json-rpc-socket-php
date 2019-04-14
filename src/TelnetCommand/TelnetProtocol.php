<?php

namespace SocketServer\TelnetCommand;

use SocketServer\ServerProtocol;

class TelnetProtocol extends ServerProtocol
{
    /**
     * @param $command
     * @return false|string
     */
    public function executeCommand($command): string
    {
        $command = str_replace(["\n"],[''], $command);

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