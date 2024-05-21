<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConfirmEmailController extends AbstractController
{
    private $manager;
    private $userRepository;

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepository)
    {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
    }

    #[Route('/confirm-email/{token}', name: 'confirm_email', methods: ['GET'])]
    public function confirmEmail(string $token): Response
    {
        $user = $this->userRepository->findOneBy(['emailVerificationToken' => $token]);

        if (!$user) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Token invalide'
                ],
            );
        }

        $user->setEmailVerified(true);
        $user->setEmailVerificationToken(null);

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            [
                'status' => true,
                'message' => 'Adresse email confirmée avec succès'
            ],
        );
    }
}
