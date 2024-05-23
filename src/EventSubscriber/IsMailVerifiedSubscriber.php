<?php

namespace App\EventSubscriber;

use App\Service\IsMailVerifiedService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;


class IsMailVerifiedSubscriber implements EventSubscriberInterface
{
private $emailVerificationService;

    public function __construct(IsMailVerifiedService $isMailVerifiedService)
    {
        $this->emailVerificationService = $isMailVerifiedService;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // Define the routes that require email verification
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
            if (!$this->emailVerificationService->isMailVerified()) {
                $response = new JsonResponse(['message' => "Email non vérifié. Veuillez cliquer sur lien de vérification dans l'email envoyé lors de l'inscription. Sans cette action vous ne pourrez pas accéder à ce service"], 403);
                $event->setResponse($response);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
