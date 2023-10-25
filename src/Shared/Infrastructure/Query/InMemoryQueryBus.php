<?php

namespace App\Shared\Infrastructure\Query;

use App\Shared\Application\Query\Query;
use App\Shared\Application\Query\QueryBus;
use App\Shared\Application\Query\Response;
use App\Shared\Infrastructure\HandlerBuilder;
use InvalidArgumentException;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class InMemoryQueryBus implements QueryBus
{
    private MessageBus $bus;

    public function __construct(iterable $queryHandlers)
    {
        $this->bus = new MessageBus([
            new HandleMessageMiddleware(
                new HandlersLocator(
                    HandlerBuilder::fromCallables($queryHandlers),
                ),
            ),
        ]);
    }

    public function ask(Query $query): ?Response
    {
        try {
            $stamp = $this->bus->dispatch($query)->last(HandledStamp::class);
            return $stamp->getResult();
        } catch (NoHandlerForMessageException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }
}