<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\TurnoverCheckService;

class TurnoverCheckController extends AbstractController
{

    private $turnoverCheckService;

    public function __construct(TurnoverCheckService $turnoverCheckService)
    {
        $this->turnoverCheckService = $turnoverCheckService;
    }


    #[Route('/turnover/check', name: 'app_turnover_check')]
    public function checkTurnover(): Response
    {
     try {
            $this->turnoverCheckService->checkAndNotifyUsers();
            return new Response('Notification emails have been sent successfully.');
        } catch (\Exception $e) {
            return new Response('An error occurred: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    
}
