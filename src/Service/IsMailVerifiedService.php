<?php

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;


class IsMailVerifiedService
{
    
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function isMailVerified(): bool
    {
        $user = $this->security->getUser();

        if (!$user) {
            return false;
        }

        return $user->isEmailVerified();
    }







}