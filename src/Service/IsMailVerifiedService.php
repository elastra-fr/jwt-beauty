<?php

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;



class IsMailVerifiedService
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Service pour vérifier si l'utilisateur est connecté et si son email est vérifié
     * @return bool
     */
    public function isMailVerified(): bool
    {
        /** @var User|null $user */
        $user = $this->security->getUser();


        if (!$user) {
            return false;
        }

        return $user->isEmailVerified();
    }
}
