<?php

namespace SocketServer;

class Config
{
    /** @var array */
    private $config;
    /** @var string */
    private $configFile;

    public function __construct(string $configFile)
    {
        $this->configFile = $configFile;
        $this->reloadConfig();
    }

    public function get(string $key): ?string
    {
        return $this->config[$key] ?? null;
    }

    public function setOrOverwrite(string $key, string $value): void
    {
        $this->reloadConfig();
        $this->config[$key] = $value;
        $this->writeConfig($this->config);
        $this->reloadConfig();
    }

    private function reloadConfig()
    {
        $dataFile     = file_get_contents($this->configFile);
        $this->config = json_decode($dataFile, true);
    }

    private function writeConfig(array $config)
    {
        $config = json_encode($config);
        file_put_contents($this->configFile, $config);
    }
}
