<?php

class Response
{
    public static function success(array $data = [], string $message = ''): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    public static function error(string $message, array $data = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data
        ];
    }
}