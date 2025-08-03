<?php

namespace App\Shared;


use App\Domain\Auth\AuthUser;

class Request
{

    private static ?array $testJson = null;

    private static ?AuthUser $authUser = null;

    public static function get(string $key, $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public static function json(): array
    {
        if (self::$testJson !== null) {
            return self::$testJson;
        }
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    public static function header(string $key): ?string
    {
        $key = strtoupper(str_replace('-', '_', $key));
        $serverKey = 'HTTP_' . $key;

        return $_SERVER[$serverKey] ?? null;
    }

    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public static function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public static function getAuthUser(): ?AuthUser
    {
        return self::$authUser;
    }

    public static function setAuthUser(?AuthUser $authUser): void
    {
        self::$authUser = $authUser;
    }


    public static function setTestJson(array $data): void
    {
        self::$testJson = $data;
    }

    public static function reset(): void
    {
        self::$testJson = null;
        self::$authUser = null;
    }
}