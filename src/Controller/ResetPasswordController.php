<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\ResetPasswordTokenValidator;
use App\Service\JsonResponseNormalizer;
use App\Form\ResetPasswordType;
use App\Service\PasswordValidatorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;


class ResetPasswordController extends AbstractController
{
    private UserRepository $userRepository;
    private ResetPasswordTokenValidator $resetPasswordTokenValidator;
    private JsonResponseNormalizer $jsonResponseNormalizer;
    private PasswordValidatorService $passwordValidatorService;

    private UserPasswordHasherInterface $passwordEncoder;  

    private EntityManagerInterface $manager;  

    public function __construct(UserRepository $userRepository, ResetPasswordTokenValidator $resetPasswordTokenValidator, JsonResponseNormalizer $jsonResponseNormalizer, PasswordValidatorService $passwordValidatorService, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $manager)
    {
        $this->userRepository = $userRepository;
        $this->resetPasswordTokenValidator = $resetPasswordTokenValidator;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
        $this->passwordValidatorService = $passwordValidatorService;
        //$this->passwordEncoder = $passwordEncoder;
        $this->passwordEncoder = $passwordEncoder;
        $this->manager = $manager;
    }
    
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function index(string $token, Request $request): Response
    {
        $user = $this->resetPasswordTokenValidator->validateToken($token);

        if (!$user) {
            $response = $this->jsonResponseNormalizer->respondError('BAD_REQUEST', 'Token invalide', 400);
            return $response;
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $plainPassword = $data['newPassword'];

            $passwordErrors = $this->passwordValidatorService->isPasswordComplex($plainPassword);
          

        


            if (!empty($passwordErrors)) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial. Veuillez respecter ces critères : '.implode(', ', $passwordErrors));
                 } 
            else {
                $hashedPassword = $this->passwordEncoder->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $user->setPasswordResetToken(null);
                $user->setLoginAttempts(0);
                $user->setPasswordResetInProgress(false);

                $this->manager->persist($user);
                $this->manager->flush();
                //$entityManager->persist($user);
                //$entityManager->flush();

                $this->addFlash('success', 'Mot de passe changé avec succès');
                
            }
        }

        return $this->render('reset_password/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
