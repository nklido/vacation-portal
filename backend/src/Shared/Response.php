<?php namespace App\Shared;

class Response
{
    public static function json(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    public static function error(string $message, int $status = 400): JsonResponse
    {
        return new JsonResponse(['error' => $message], $status);
    }
}
