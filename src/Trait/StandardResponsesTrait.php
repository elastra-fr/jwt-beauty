<?php

namespace App\Trait;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\JsonResponseNormalizer;

trait StandardResponsesTrait
{
    private JsonResponseNormalizer $jsonResponseNormalizer;

    public function setJsonResponseNormalizer(JsonResponseNormalizer $jsonResponseNormalizer): void
    {
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
    }

    protected function respondNotAuthenticated(): JsonResponse
    {
        return $this->jsonResponseNormalizer->respondError('NOT_AUTHENTICATED', 'Utilisateur non authentifié', 401);
    }

    protected function respondSalonNotFound(): JsonResponse
    {
        return $this->jsonResponseNormalizer->respondError('SALON_NOT_FOUND', 'Salon non trouvé', 404);
    }

    protected function respondUserSalonsNotFound(): JsonResponse
    {
        return $this->jsonResponseNormalizer->respondSuccess(200, ['message' => 'Aucun salon trouvé pour cet utilisateur']);
    }

    protected function respondForbidden(): JsonResponse
    {
        return $this->jsonResponseNormalizer->respondError('FORBIDDEN', 'Accès non autorisée', 403);
    }

    

    protected function respondNotOwner(): JsonResponse
    {
        return $this->jsonResponseNormalizer->respondError('NOT_OWNER', 'Vous n\'êtes pas propriétaire de ce salon', 403);
    }

    protected function respondAlreadyExists(): JsonResponse
    {
        return $this->jsonResponseNormalizer->respondError('ALREADY_EXISTS', 'Le chiffre d\'affaires pour ce mois existe déjà', 403);
    }

    protected function respondConfirmedEmail(): JsonResponse
    {
        return $this->jsonResponseNormalizer->respondSuccess(200, ['message' => 'Adresse email confirmée avec succès']);
    }

    protected function respondInvalidToken(): JsonResponse
    {
        return $this->jsonResponseNormalizer->respondError('INVALID_TOKEN', 'Token invalide', 400);
    }

    protected function respondEmailChangeSuccess(): JsonResponse
    {
        return $this->jsonResponseNormalizer->respondSuccess(200, ['message' => 'Email changé avec succès - Veuillez vérifier votre nouvelle adresse email pour confirmer le changement']);
    }

    protected function respondEmailAlreadyExist(): JsonResponse
    {
        return $this->jsonResponseNormalizer->respondError('EMAIL_ALREADY_EXIST', 'Cette adresse email existe déjà', 400);
    }
}
