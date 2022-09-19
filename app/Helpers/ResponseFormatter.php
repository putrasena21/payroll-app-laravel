<?php

namespace App\Helpers;

class ResponseFormatter {
    protected static $response = [
        'statusCode' => null,
        'succes' => null,
        'message' => null,
        'data' => null
    ];

    public static function createResponse($statusCode = null, $message = null, $data = null)
    {
        if($statusCode >= 400) {
            self::$response['succes'] = false;
        } else {
            self::$response['succes'] = true;
        }
        
        self::$response['statusCode'] = $statusCode;
        self::$response['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['statusCode']);
    }
}
