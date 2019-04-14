<?php

namespace JsonRpcServer;

class SubstractAction
{
    public static function methodName(): string
    {
        return 'substract';
    }

    public function __invoke($num1, $num2)
    {
        return $num1 - $num2;
    }
}
