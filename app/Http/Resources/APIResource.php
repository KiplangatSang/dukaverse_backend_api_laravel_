<?php
namespace App\Http\Resources;

use Beste\Json;
use Illuminate\Http\JsonResponse;

class ApiResource
{
    public static function success(?array $data, string $message, int $code =200)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public static function error(string $message, int $code, $errors = null)
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }



}
