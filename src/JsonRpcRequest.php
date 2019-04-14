<?php

namespace JsonRpcServer;

use Exception;

class JsonRpcRequest
{
    /** @var string */
    private $version;
    /** @var string */
    private $id;
    /** @var string */
    private $method;
    /** @var array */
    private $params;

    public function __construct(
        string $version,
        string $id,
        string $method,
        array $params
    ) {
        $this->version = $version;
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * @param string $json
     * @return JsonRpcRequest
     * @throws Exception
     */
    public static function buildFromJsonRequest(string $json): self
    {
        $decodeJson = self::validateRpcFormat($json);

        return new self(
            $decodeJson['jsonrpc'] ?? '1.0',
            $decodeJson['id'] ?? '',
            $decodeJson['method'] ?? '',
            $decodeJson['params'] ?? []
        );
    }

    /**
     * @param string $json
     * @return array
     * @throws Exception
     */
    private static function validateRpcFormat(string $json): array
    {
        $formatJson = str_replace(["\n"],[''], $json);

        return json_decode($formatJson, true);
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function params(): array
    {
        return $this->params;
    }
}
