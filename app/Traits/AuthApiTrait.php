<?php

namespace App\Traits;

trait AuthApiTrait
{
    public function successResponse($data = null, $message = 'Success', $status = 200)
    {
        return response()->json([
            'status'  => $status < 300,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }
}
