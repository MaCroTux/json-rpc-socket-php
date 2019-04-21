<?php

namespace SocketServer;

trait TerminalTrait
{
    public function terminalCommandExtend(
        Connection $connection,
        string $query,
        ?Config $config = null
    ): ?string {

        $commands = [
            '\\info',
            '\\exit',
            '\\users',
            '\\change_pass',
            '\\help',
        ];

        if (!in_array($query, $commands)) {
            return null;
        }

        if ($query === '\\help') {
            return $this->commandsList();
        }

        if ($query === '\\info') {
            $this->info($connection);
            return '';
        }

        if ($query === '\\exit') {
            if ($this->password !== null) {
                $this->protect = true;
            }
            $connection->closeConnect();
            return 'Closed session';
        }

        if ($query === '\\users') {
            return $this->usersInfo($connection->getAllConnections());
        }

        if (strpos($query, '\\kick') === 0) {
            list($query, $clientId) = explode(' ', $query);
            $connection->kickConnect($clientId);
            return "User {$clientId} kicked";
        }

        if (strpos($query, '\\change_pass') === 0) {
            $commandList = explode(' ', $query);
            $oldPass = $commandList[1];
            $newPass = $commandList[2] ?? null;
            if ($this->password === null) {
                $this->password = $oldPass;
            } else if ($this->password === $oldPass) {
                $this->password = $newPass;
            }

            $config->setOrOverwrite('password', $this->password);
            $this->loadConfigFile();

            return "Password change";
        }
    }

    public function info(Connection $connection)
    {
        $client = $connection->client();
        $client
            ->getLoop()
            ->onTick(function() use ($client) {
                $info = sprintf("\n%s\n", str_repeat('-', 42));
                $info .= sprintf("%s\n", 'Client info:');
                $info .= sprintf("%s\n", str_repeat('-', 42));
                $info .= sprintf("%-20s%s\n", 'Resource ID:', '#' . $client->getResourceId());
                $info .= sprintf("%-20s%s\n", 'Local endpoint:', $client->getLocalEndpoint());
                $info .= sprintf("%-20s%s\n", 'Local protocol:', $client->getLocalProtocol());
                $info .= sprintf("%-20s%s\n", 'Local address:', $client->getLocalAddress());
                $info .= sprintf("%-20s%s\n", 'Local host:', $client->getLocalHost());
                $info .= sprintf("%-20s%s\n", 'Local port:', $client->getLocalPort());
                $info .= sprintf("%-20s%s\n", 'Remote endpoint:', $client->getRemoteEndpoint());
                $info .= sprintf("%-20s%s\n", 'Remote protocol:', $client->getRemoteProtocol());
                $info .= sprintf("%-20s%s\n", 'Remote address:', $client->getRemoteAddress());
                $info .= sprintf("%-20s%s\n", 'Remote host:', $client->getRemoteHost());
                $info .= sprintf("%-20s%s\n", 'Remote port:', $client->getRemotePort());
                $info .= sprintf("%s\n", str_repeat('-', 42));

                $client->write($info);
            });
    }

    public function usersInfo(array $connections)
    {
        $numClients = count($connections);
        $info = '';
        $info .= sprintf("\n%-20s%s\n", 'Client connect:', $numClients);

        $info .= sprintf(
            "%-10s%-10s%-10s %s\n",
            'Connect',
            'Client id',
            'Port',
            'Date'
        );

        $info .= sprintf(
            "%-10s%-10s%-10s %s\n",
            str_repeat('-',9),
            str_repeat('-',9),
            str_repeat('-',9),
            str_repeat('-',18)
        );

        /** @var ServerProtocol $protocol */
        foreach ($connections as $connection) {
            $info .= sprintf(
                "%-10s%-10s%-10s %s\n",
                $connection['isOpen'] === true ? 'Open' : 'Closed',
                $connection['clientId'],
                $connection['port'],
                $connection['date']
            );
        }

        return $info;
    }

    public function commandsList(): string
    {
        $commands = $this->rpcCommands;

        $commandList = "\n";

        $commandList .= sprintf(
            "%-15s%s\n",
            'Command',
            'Description'
        );

        $commandList .= sprintf(
            "%-15s%s\n",
            str_repeat('-',14),
            str_repeat('-',30)
        );

        /** @var Command $command */
        foreach ($commands as $command) {
            $commandList .= sprintf(
                "%-15s%s\n",
                $command->methodName(),
                $command->description()
            );
        }

        return $commandList;
    }
}