<?php

$fileName = $argv[1];

$json = file_get_contents($fileName);

$commands = json_decode($json, true);

$maxCommandLeng = 0;
foreach ($commands as $alias => $command) {
    if (strlen($command) > $maxCommandLeng) {
        $maxCommandLeng = strlen($command);
    }
}

printf("%-10s %s\n", "Alias", "Command");
printf("%-10s %s\n", str_repeat('-',9), str_repeat('-',$maxCommandLeng));

$print = '';
foreach ($commands as $alias => $command) {
    $print .= sprintf("%-10s %s\n", '.'.$alias, $command);
}

echo $print;
