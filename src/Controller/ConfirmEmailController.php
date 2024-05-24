<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\JsonResponseNormalizer;

class ConfirmEmailController extends AbstractController
{
    private EntityManagerInterface $manager;
    private UserRepository $userRepository;

    private JsonResponseNormalizer $jsonResponseNormalizer;

/**
 * ConfirmEmailController constructor.
 *
 * @param EntityManagerInterface $manager
 * @param UserRepository $userRepository
 */
    public function __construct(EntityManagerInterface $manager, UserRepository $userRepository, JsonResponseNormalizer $jsonResponseNormalizer)
    {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
    }

/**
 * Cette méthode permet de confirmer l'adresse email d'un utilisateur
 * @param string $token : le token de vérification
 * @return JsonResponse : la réponse HTTP
 */

    #[Route('/confirm-email/{token}', name: 'confirm_email', methods: ['GET'])]
    public function confirmEmail(string $token): Response
    {
        $user = $this->userRepository->findOneBy(['emailVerificationToken' => $token]);

        if (!$user) {

$reponse= $this->jsonResponseNormalizer->respondError('BAD_REQUEST', 'Token invalide', 400);

            return $reponse;

        }

        $user->setEmailVerified(true);
        $user->setEmailVerificationToken(null);

        $this->manager->persist($user);
        $this->manager->flush();
        $reponse= $this->jsonResponseNormalizer->respondSuccess(200, ['message'=>'Adresse email confirmée avec succès']);
        return $reponse;

    }
}
