<?php

namespace App\Controller;


use App\Service\ResetPasswordTokenValidator;
use App\Service\JsonResponseNormalizer;
use App\Form\ResetPasswordType;
use App\Service\PasswordValidatorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\StandardResponsesTrait;


class ResetPasswordController extends AbstractController
{
    use StandardResponsesTrait;

    private ResetPasswordTokenValidator $resetPasswordTokenValidator;
    private JsonResponseNormalizer $jsonResponseNormalizer;
    private PasswordValidatorService $passwordValidatorService;

    private UserPasswordHasherInterface $passwordEncoder;

    private EntityManagerInterface $manager;

    /**
     * Constructor
     *
     * @param ResetPasswordTokenValidator $resetPasswordTokenValidator
     * @param JsonResponseNormalizer $jsonResponseNormalizer
     * @param PasswordValidatorService $passwordValidatorService
     * @param UserPasswordHasherInterface $passwordEncoder
     * @param EntityManagerInterface $manager
     */
    public function __construct(ResetPasswordTokenValidator $resetPasswordTokenValidator, JsonResponseNormalizer $jsonResponseNormalizer, PasswordValidatorService $passwordValidatorService, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $manager)
    {

        $this->resetPasswordTokenValidator = $resetPasswordTokenValidator;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
        $this->passwordValidatorService = $passwordValidatorService;
        $this->passwordEncoder = $passwordEncoder;
        $this->manager = $manager;
    }

    /**
     * Permet à l'utilisateur de réinitialiser son mot de passe en le redirigeant vers un formulaire de réinitialisation. Le contrôleur vérifie si le token est valide et si oui, affiche le formulaire de réinitialisation.
     * Le contrôleur vérifie si le mot de passe est valide à l'aide du service PasswordValidator et si oui, le change.
     * @param string $token
     * @param Request $request
     * @return Response
     */
#[Route('/reset-password/{token}', name: 'app_reset_password')]
public function index(string $token, Request $request): Response
{
    $user = $this->resetPasswordTokenValidator->validateToken($token);

    if (!$user) {
        return $this->respondInvalidToken();
    }

    $form = $this->createForm(ResetPasswordType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            $data = $form->getData();
            $plainPassword = $data['newPassword'];

            $passwordErrors = $this->passwordValidatorService->isPasswordComplex($plainPassword);

            if (!empty($passwordErrors)) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial. Veuillez respecter ces critères : ' . implode(', ', $passwordErrors));
                // Renvoyer un statut 422 si les critères de validation de mot de passe ne sont pas respectés
                return $this->render('reset_password/index.html.twig', [
                    'form' => $form->createView(),
                ], new Response('', 422));
            } else {
                $hashedPassword = $this->passwordEncoder->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $user->setPasswordResetToken(null);
                $user->setLoginAttempts(0);
                $user->setPasswordResetInProgress(false);

                $this->manager->persist($user);
                $this->manager->flush();

                $this->addFlash('success', 'Mot de passe changé avec succès');

                return $this->redirectToRoute('app_login');
            }
        } else {
            // Renvoyer un statut 422 si le formulaire n'est pas valide
            return $this->render('reset_password/index.html.twig', [
                'form' => $form->createView(),
            ], new Response('', 422));
        }
    }

    // Si le formulaire n'est pas soumis, rendre la vue normale sans statut 422
    return $this->render('reset_password/index.html.twig', [
        'form' => $form->createView(),
    ]);
}


}
