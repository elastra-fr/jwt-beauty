<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;


class ResetPasswordTokenValidator
{
    private UserRepository $userRepository;

/**
 * ResetPasswordTokenValidator constructor.
 *
 * @param UserRepository $userRepository
 */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

/**
 * Valider un token de réinitialisation de mot de passe
 *
 * @param string $token
 * @return User|null
 */
    public function validateToken(string $token): ?User
    {
        $user = $this->userRepository->findOneBy(['password_reset_token' => $token]);

        if ($user) {
            return $user;
        }

        return null;
    }
}
