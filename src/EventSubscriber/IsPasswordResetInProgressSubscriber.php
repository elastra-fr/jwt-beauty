<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\UserRepository;
use App\Service\IsResetInProgressService;
use App\Service\JsonResponseNormalizer;
use Symfony\Component\BrowserKit\Response;


class IsPasswordResetInProgressSubscriber implements EventSubscriberInterface
{
    private $userRepository;
    private $isResetInProgressService;

    private $jsonResponseNormalizer;

    public function __construct(UserRepository $userRepository, IsResetInProgressService $isResetInProgressService, JsonResponseNormalizer $jsonResponseNormalizer)
    {
        $this->userRepository = $userRepository;
        $this->isResetInProgressService = $isResetInProgressService;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
    }

    public function onKernelRequest(RequestEvent $event)
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

          
                //$response = new JsonResponse(["success"=>false, 'error'=>['code'=>'UNAUTHORIZED',  'message' => 'Réinitialisation du mot de passe en cours. Veuillez vérifier vos emails pour terminer le processus. Tant que la procédure ne sera pas terminée, vous ne pourrez pas accéder à ces services']], 403);
                $response = $this->jsonResponseNormalizer->respondError('UNAUTHORIZED', 'Réinitialisation du mot de passe en cours. Veuillez vérifier vos emails pour terminer le processus. Tant que la procédure ne sera pas terminée, vous ne pourrez pas accéder à ces services', 403);
                $event->setResponse($response);
            }

        }
    }

    public static function getSubscribedEvents()
    {
        // Écouter l'événement KernelEvents::REQUEST
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
