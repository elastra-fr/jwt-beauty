<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\MailerService;
use Psr\Log\LoggerInterface;

class LoginFailureSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    private $userRepository;
    private $requestStack;
    private $mailerService;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, RequestStack $requestStack, MailerService $mailerService, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->requestStack = $requestStack;
        $this->mailerService = $mailerService;
        $this->logger = $logger;
    }



public static function getSubscribedEvents()
{
    return [
        'lexik_jwt_authentication.on_authentication_failure' => 'onAuthenticationFailure',
    ];
}



    public function onAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $this->logger->info('Authentication failure event triggered.');

        // Récupérer la requête à partir de l'événement
        $request = $event->getRequest();
        if (!$request) {
            $this->logger->error('No request found in the event.');
            return;
        }

        // Récupérer les données de connexion à partir de la requête
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if ($email) {
            $this->logger->info('Attempted login with email: ' . $email);

            // Trouver l'utilisateur par email
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Mettre à jour la propriété login_attempt
                $user->setLoginAttempts($user->getLoginAttempts() + 1);

                $this->logger->info('Updated login attempt for user: ' . $email . ' to ' . $user->getLoginAttempts());

                // Vérifier le nombre de tentatives de connexion et si supérieur ou égal à 5, envoyer un email à l'utilisateur pour l'informer
                if ($user->getLoginAttempts() > 5) {
                    $this->mailerService->sendEmail(
                        $user->getEmail(),
                        'Tentatives de connexion infructueuses trop nombreuses',
                        'Veuillez réinitialiser votre mot de passe en utilisant le lien ci-dessous.'
                    );

                    $user->setPasswordResetInProgress(true);
                    $this->logger->info('Sent warning email to user: ' . $email);
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            } else {
                $this->logger->error('No user found with email: ' . $email);
            }
        } else {
            $this->logger->error('No email found in the request data.');
        }
    }
}
