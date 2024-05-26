<?php

namespace App\EventSubscriber;

use App\Service\IsMailVerifiedService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Service\JsonResponseNormalizer;


class IsMailVerifiedSubscriber implements EventSubscriberInterface
{
    private  IsMailVerifiedService $isMailVerifiedService;

    private JsonResponseNormalizer $jsonResponseNormalizer;

    /**
     * @param IsMailVerifiedService $isMailVerifiedService
     * @param JsonResponseNormalizer $jsonResponseNormalizer
     */

    public function __construct(IsMailVerifiedService $isMailVerifiedService, JsonResponseNormalizer $jsonResponseNormalizer)
    {
        $this->isMailVerifiedService = $isMailVerifiedService;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
    }

    /**
     * Vérifie si l'email est vérifié avant d'accéder à certaines routes
     *
     * @param RequestEvent $event
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // Definie les routes qui nécessitent une vérification de l'email. Le client peut décider que certaines routes nécessitent une vérification de l'email et d'autres non pour rester flexible et accessible en lecture seule.
        $routesRequiringVerification = [
            'app_salon',
            'app_create_salon',
            'app_salon_show',
            'app_salon_update',
            'app_turnover_add',
            'app_turnover_show',
            'get_user',
            'update_user',

        ];

        if (in_array($route, $routesRequiringVerification)) {
            if (!$this->isMailVerifiedService->isMailVerified()) {
                $emailNotVerified = $this->jsonResponseNormalizer->respondError('email_not_verified', "Email non vérifié. Veuillez cliquer sur lien de vérification dans l'email envoyé lors de l'inscription. Sans cette action vous ne pourrez pas accéder à ce service", 403);
                $event->setResponse($emailNotVerified);
            }
        }
    }

    /**
     * Définit les événements à écouter 
     * @return array
     * 
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
