<?php

function dispatch(callable $handler, array $params = []): void
{
    \App\Shared\ResponseDispatcher::dispatch($handler, $params);
}