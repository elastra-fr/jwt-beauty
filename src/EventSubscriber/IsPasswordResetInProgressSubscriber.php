<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Service\IsResetInProgressService;
use App\Service\JsonResponseNormalizer;




class IsPasswordResetInProgressSubscriber implements EventSubscriberInterface
{
    private IsResetInProgressService $isResetInProgressService;

    private JsonResponseNormalizer $jsonResponseNormalizer;

    /**
     * IsPasswordResetInProgressSubscriber constructor.
     * @param IsResetInProgressService $isResetInProgressService
     * @param JsonResponseNormalizer $jsonResponseNormalizer
     * 
     */

    public function __construct(IsResetInProgressService $isResetInProgressService, JsonResponseNormalizer $jsonResponseNormalizer)
    {

        $this->isResetInProgressService = $isResetInProgressService;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
    }


    /**
     * Cette méthode permet de vérifier si une réinitialisation du mot de passe est en cours pour l'utilisateur en cours. Si c'est le cas, une réponse JSON est renvoyée avec un code d'erreur.
     *
     * @param RequestEvent $event
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        // Récupérer la requête entrante
        $request = $event->getRequest();

        // Récupérer le chemin de la requête
        $path = $request->getPathInfo();

        // Vérifier si le chemin commence par "/api/"
        if (strpos($path, '/api/') === 0) {
            // Récupérer l'utilisateur en cours

            // Si une réinitialisation du mot de passe est en cours pour cet utilisateur, renvoyer une réponse JSON avec un code d'erreur
            if ($this->isResetInProgressService->isResetInProgress()) {

                $response = $this->jsonResponseNormalizer->respondError('UNAUTHORIZED', 'Réinitialisation du mot de passe en cours. Veuillez vérifier vos emails pour terminer le processus. Tant que la procédure ne sera pas terminée, vous ne pourrez pas accéder à ces services', 403);
                $event->setResponse($response);
            }
        }
    }

    /**
     * Cette méthode permet de définir les événements écoutés par le subscriber
     * @return array
     */

    public static function getSubscribedEvents(): array
    {
        // Écouter l'événement KernelEvents::REQUEST
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
