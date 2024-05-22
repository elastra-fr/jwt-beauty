<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SalonController extends AbstractController
{
    #[Route('/salon', name: 'app_salon')]
    public function index(): Response
    {
        return $this->render('salon/index.html.twig', [
            'controller_name' => 'SalonController',
        ]);
    }
}
