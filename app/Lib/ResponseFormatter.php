<?php

namespace App\Lib;

use Symfony\Component\HttpFoundation\Response;

class ResponseFormatter
{

    /**
     * Response success structure standardization.
     * 
     * @param string $message
     * @param array $data
     * @param int $code
     * @return \Illuminate\Http\Response
     */
    public function success($message = "", $data = [], $code = Response::HTTP_OK)
    {

        return response()->json([
            "status" => true,
            "message" => $message,
            "data" => $data,
            "error" => null,
        ], $code);
    }

    /**
     * Response fail structure standardization failed.
     * 
     * @param string $message
     * @param array $error
     * @param int $code
     * @return \Illuminate\Http\Response
     */
    public function fail($message = "", $error = [], $code = Response::HTTP_BAD_REQUEST)
    {
        return response()->json([
            "status" => false,
            "message" => $message,
            "data" => null,
            "error" =>  $error,
        ], $code);
    }
}
