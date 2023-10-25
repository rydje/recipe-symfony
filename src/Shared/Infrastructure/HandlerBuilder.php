<?php

namespace App\Shared\Infrastructure;

use ReflectionClass;

final class HandlerBuilder
{
    /**
     * @throws ReflectionException
     */
    public static function fromCallables(iterable $callables) : array
    {
        $callablesHandlers = [];

        foreach ($callables as $callable) {
            $envelop = self::extractFirstParam($callable);

            if (! array_key_exists($envelop, $callablesHandlers)) {
                $callablesHandlers[$envelop] = [];
            }

            $callablesHandlers[$envelop][] = $callable;
        }

        return $callablesHandlers;
    }

    /**
     * @throws ReflectionException
     */
    private static function extractFirstParam(object|string $class) : string|null
    {
        $reflection = new ReflectionClass($class);
        $method     = $reflection->getMethod('__invoke');

        if ($method->getNumberOfParameters() === 1) {
            return $method->getParameters()[0]->getType()?->getName();
        }

        return null;
    }
}