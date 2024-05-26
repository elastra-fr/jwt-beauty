<?php

namespace App\Service;

class PasswordValidatorService
{
    /**
     * Vérifie si le mot de passe est complexe et répond aux critères suivants : 
     * - au moins une majuscule
     * - au moins une minuscule
     * - au moins un chiffre
     * - au moins un caractère spécial
     * - au moins 8 caractères
     *
     * @param string $password
     * @return array
     */
    public function isPasswordComplex(string $password): array
    {
        $errors = [];

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'au moins une majuscule';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'au moins une minuscule';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'au moins un chiffre';
        }
        if (!preg_match('/[\W]/', $password)) {
            $errors[] = 'au moins un caractère spécial';
        }
        if (strlen($password) < 8) {
            $errors[] = 'au moins 8 caractères';
        }

        return $errors;
    }
}
