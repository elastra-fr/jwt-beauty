<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseNormalizer
{
    /**
     * Fonction pour normaliser les réponses en cas d'erreur
     *
     * @param string $errorCode
     * @param string $errorMessage
     * @param integer $HttpStatus
     * @return JsonResponse
     */
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

/**
 * Fonction pour normaliser les réponses en cas de succès
 *
 * @param integer $httpStatus
 * @param [type] $data
 * @return JsonResponse
 */
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