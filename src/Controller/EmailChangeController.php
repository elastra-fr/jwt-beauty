<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\MailerService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class EmailChangeController extends AbstractController
{


    private EntityManagerInterface $manager;
    private UserRepository $userRepository;

    private MailerService $mailerService;

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepository, MailerService $mailerService)
    {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
        $this->mailerService = $mailerService;
    }

    #[Route('/email/change/confirm/{token}', name: 'app_confirm_email_change')]
    public function confirmEmailChange(string $token): Response
    {
        
        $user = $this->userRepository->findOneBy(['email_change_token' => $token]);

        if (!$user) {
            return $this->json(['message' => 'Token invalide'], 400);
        }

        $user->setEmail($user->getNewEmail());
        $user->setNewEmail(null);
        $user->setEmailChangeToken(null);
        $user->setEmailVerified(false);
        $user->setEmailVerificationToken(bin2hex(random_bytes(32)));
        $this->manager->persist($user);
        $this->manager->flush();

            $newEmailConfirmationLink = $this->generateUrl('confirm_email', ['token' => $user->getEmailVerificationToken()], UrlGeneratorInterface::ABSOLUTE_URL);
                   $emailBody = sprintf(
                    'Bonjour %s,<br><p>Votre inscription a été effectuée avec succès. Veuillez cliquer sur le lien suivant pour confirmer votre adresse email : <a href="%s">Confirmer votre adresse email</a></p>',
                    $user->getFirstName(),
                     $newEmailConfirmationLink
);

            $this->mailerService->sendEmail(
            $user->getEmail(),
            'Inscription réussie',
            $emailBody
            
        );


        return $this->json(['message' => 'Email changé avec succès - Veuillez confirmer la nouvelle adresse mail'], 200);

    

    }

    #[Route('/email/change/cancel/{token}', name: 'app_cancel_email_change')]

public function cancelEmailChange(string $token): Response
{
    
    $user = $this->userRepository->findOneBy(['email_change_token' => $token]);

    if (!$user) {
        return $this->json(['message' => 'Token invalide'], 400);
    }

    $user->setEmailChangeToken(null);
    $user->setNewEmail(null);

    //Déclenchement d'un reset du password par mesure de sécurité

    $user->setPasswordResetInProgress(true);
    $user->generatePasswordResetToken();

    $confirmationLink = $this->generateUrl('app_reset_password', ['token' => $user->getPasswordResetToken()], UrlGeneratorInterface::ABSOLUTE_URL);

    $this->mailerService->sendEmail(
        $user->getEmail(),
        'Changement d\'email annulé',
        'Votre demande de changement d\'email a été annulée. Pour des raisons de sécurité, nous vous devez réinitialiser votre mot de passe en cliquant sur le lien suivant : <a href="' . $confirmationLink . '">Réinitialiser votre mot de passe</a>'
    );


 
    $this->manager->persist($user);


    $this->manager->flush();

    return $this->json(['message' => 'Changement d\'email annulé. Par mesure de sécurité nous vous demandons de reinitialiser votre mot de passe. Veuillez consulter vos emails et suivre la procédure'], 200);
    
}

}
