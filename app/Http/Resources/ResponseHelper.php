<?php
namespace App\Http\Resources;

class ResponseHelper
{
    public static function respond($data = null, $message = 'Successful response', $httpCode = 200)
    {
        return [
            'data'     => $data,
            'httpCode' => $httpCode,
            "message"  => $message,
        ];
    }

    public static function error($message = "An error occurred", $error = null, $httpCode = 500)
    {
        return [
            'error'    => $error,
            'httpCode' => $httpCode,
            "message"  => $message,
        ];
    }
}
