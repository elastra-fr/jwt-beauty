<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TurnoverController extends AbstractController
{
    #[Route('/turnover', name: 'app_turnover')]
    public function index(): Response
    {
        return $this->render('turnover/index.html.twig', [
            'controller_name' => 'TurnoverController',
        ]);
    }
}
