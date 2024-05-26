<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Service\MailerService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Service\JsonResponseNormalizer;
use App\Trait\StandardResponsesTrait;

class LoginFailureSubscriber implements EventSubscriberInterface
{
    use StandardResponsesTrait;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    private MailerService $mailerService;
    private LoggerInterface $logger;

    private RouterInterface $router;

    private JsonResponseNormalizer $jsonResponseNormalizer;

    /**
     * LoginFailureSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param MailerService $mailerService
     * @param LoggerInterface $logger
     * @param RouterInterface $router
     * @param JsonResponseNormalizer $jsonResponseNormalizer
     */
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, MailerService $mailerService, LoggerInterface $logger, RouterInterface $router, JsonResponseNormalizer $jsonResponseNormalizer)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->mailerService = $mailerService;
        $this->logger = $logger;
        $this->router = $router;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
    }


    /**
     * Retourne les événements auxquels on s'abonne
     * @return array
     * 
     */
    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_authentication_failure' => 'onAuthenticationFailure',
        ];
    }


    /**
     * Cette méthode est appelée lorsqu'une tentative de connexion échoue
     * et incrémente le nombre de tentatives de connexion pour un utilisateur donné.
     * si le nombre de tentatives de connexion est supérieur ou égal à 5, un email est envoyé à l'utilisateur
     * pour l'informer et lui demander de réinitialiser son mot de passe.
     *
     * @param AuthenticationFailureEvent $event
     * @return void
     */
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


                    $confirmationLink = $this->router->generate('app_reset_password', ['token' => $user->getPasswordResetToken()], UrlGeneratorInterface::ABSOLUTE_URL);

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
