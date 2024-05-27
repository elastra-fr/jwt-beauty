<?php
/************Ce controleur ne sert qu'à tester le service dans le cadre du developpement**********/

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\TurnoverCheckService;
use App\Service\JsonResponseNormalizer;

class TurnoverCheckController extends AbstractController
{

    private TurnoverCheckService $turnoverCheckService;

    private JsonResponseNormalizer $jsonResponseNormalizer;

    /**
     * TurnoverCheckController constructor.
     *
     * @param TurnoverCheckService $turnoverCheckService
     */
    public function __construct(TurnoverCheckService $turnoverCheckService, JsonResponseNormalizer $jsonResponseNormalizer)
    {
        $this->turnoverCheckService = $turnoverCheckService;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
    }

    /**
     * Vérifie le chiffre d'affaires si le chiffre d'affaires du mois précédent est inférieur à été déclaré à l'aide du service TurnoverCheckService et envoie des notifications par e-mail aux utilisateurs concernés.
     * @return Response : la réponse HTTP
     */

 //   #[Route('/turnover/check', name: 'app_turnover_check')]
    public function checkTurnover(): Response
    {
        try {
            $this->turnoverCheckService->checkAndNotifyUsers();
            $SuccessNotification = $this->jsonResponseNormalizer->respondSuccess(200, ['message' => 'Les e-mails de notification ont été envoyés avec succès.']);
            return $SuccessNotification;
        } catch (\Exception $e) {
            $ErrorNotification = $this->jsonResponseNormalizer->respondError('INTERNAL_SERVER_ERROR', 'Une erreur est survenue: ' . $e->getMessage(), 500);
            return $ErrorNotification;
        }
    }
}
