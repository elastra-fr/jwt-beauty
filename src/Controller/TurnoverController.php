<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Turnover;

class TurnoverController extends AbstractController
{

private $security;

private $manager;

public function __construct(Security $security,  EntityManagerInterface $manager)
{
    $this->security = $security;
    $this->manager = $manager;
}

/************Insertion d'un chiffre d'affaires pour un salon *****************/

#[Route('/api/turnover-insert', name: 'app_turnover_add', methods: ['POST'])]

public function addTurnover(Request $request): Response
{
    

}




}
