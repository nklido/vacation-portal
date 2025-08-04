<?php

namespace App\Shared;

class JsonResponse
{
    private mixed $data;
    private int $status;

    public function __construct(mixed $data, int $status = 200)
    {
        $this->data = $data;
        $this->status = $status;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            header('Content-Type: application/json; charset=utf-8');
        }
        if ($this->data !== null) {
            echo json_encode($this->data);
        }
    }
}
