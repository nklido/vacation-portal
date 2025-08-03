<?php

namespace App\Shared;

use App\Domain\Exception\UnauthorizedException;

class ResponseDispatcher
{
    public static function dispatch(callable $handler, array $params = []): void
    {
        try {
            $result = $handler(...$params);

            if ($result instanceof JsonResponse) {
                $result->send();
            } elseif (is_string($result)) {
                echo $result;
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Unexpected response']);
            }
        } catch (UnauthorizedException $e) {
            Response::error($e->getMessage(), 401)->send();
        } catch (\Throwable $e) {
            Response::error($e->getMessage() , 500)->send();
        }
    }
}