<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\MailerService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class LoginFailureSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    private $userRepository;
    private $requestStack;
    private $mailerService;
    private $logger;

    private $router;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, RequestStack $requestStack, MailerService $mailerService, LoggerInterface $logger, RouterInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->requestStack = $requestStack;
        $this->mailerService = $mailerService;
        $this->logger = $logger;
        $this->router = $router;
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

                      $user->setPasswordResetInProgress(true);
                    $user->generatePasswordResetToken();

                            //$resetLink = 'https://example.com/reset-password/' . $user->getPasswordResetToken();
                            $confirmationLink = $this->router->generate('confirm_email', ['token' => $user->getPasswordResetToken()], UrlGeneratorInterface::ABSOLUTE_URL);

                    $this->mailerService->sendEmail(
                        $user->getEmail(),
                        'Trop de tentatives de connexion',
                        'Vous avez dépassé le nombre de tentatives de connexion autorisées. Veuillez cliquer sur le lien suivant pour réinitialiser votre mot de passe : <a href="' . $confirmationLink . '">Réinitialiser votre mot de passe</a>'

                        
                    );

                  
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
