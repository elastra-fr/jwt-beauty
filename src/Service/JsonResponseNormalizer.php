<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseNormalizer
{
    
        public function respondError(string $errorCode, string $errorMessage, int $HttpStatus): JsonResponse
    {
        $response = [
            'status' => 'error',
            'data' => null,
            'error' => [
                'code' => $errorCode,
                'message' => $errorMessage,
            ],
        ];

        return new JsonResponse($response, $HttpStatus);
    }

    public function respondSuccess(int $httpStatus, $data = null ): JsonResponse
    {
        $response = [
            'status' => 'success',
            'data' => $data,
            'error' => null,
        ];

    
        return new JsonResponse($response, $httpStatus);
    }




}