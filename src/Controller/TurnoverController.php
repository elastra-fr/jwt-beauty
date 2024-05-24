<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Turnover;
use App\Repository\DepartementRepository;
use App\Repository\SalonRepository;
use App\Repository\TurnoverRepository;
use PHPUnit\Util\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;

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

#[Route('/api/turnover-insert/{salonId}', name: 'app_turnover_add', methods: ['POST'])]

public function addTurnover(Request $request, SalonRepository $salonRepository, TurnoverRepository $turnoverRepository, DepartementRepository $departementRepository): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non authentifié'], 401);
        }

        $salonId = $request->get('salonId');

        //var_dump($salonId);

        $salon = $salonRepository->getSalonById($salonId);

        if (!$salon) {
            return new JsonResponse(['message' => 'Salon non trouvé'], 404);
        }

        // Comparaison des ID des utilisateurs
        if ($salon->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['message' => 'Vous n\'êtes pas propriétaire de ce salon'], 403);
        }

        $departement= $salon->getDepartement();
        $region= $departement->getRegion();

         $departmentCode = $departement->getCode();
        $departmentName = $departement->getName();

        $regionId = $region->getId();
        $regionName = $region->getRegionName();   

        //$departmentCode = $salon->getDepartement()->getCode();
        //$departmentName = $salon->getDepartement()->getName();


        
   
        

        $lastMonthFirstDay = (new DateTime('first day of last month'))->setTime(0, 0);
        $turnover = $this->manager->getRepository(Turnover::class)->findOneBy([
            'salon_id' => $salonId,
            'period' => $lastMonthFirstDay,
        ]);

        if ($turnover) {
            return new JsonResponse(['message' => 'Le chiffre d\'affaires pour ce mois existe déjà', 'status'=>false], 403);
        }

        $data = json_decode($request->getContent(), true);
        
        

        $turnover = new Turnover();
        $turnover->setTurnoverAmount($data['amount']);
        $turnover->setPeriod($lastMonthFirstDay);
        $turnover->setSalon($salon);

        $this->manager->persist($turnover);
        $this->manager->flush();

        $averageTurnoverNationWide = $turnoverRepository->getAllTurnoversAverage();
        $averageTurnoverNationWide = round($averageTurnoverNationWide, 2);

        $nationalPosition = "";
$departmentPosition = "";

        if ($data['amount'] > $averageTurnoverNationWide) {
            $nationalPosition = "Votre chiffre d'affaires déclaré est supérieur à la moyenne nationale qui est de $averageTurnoverNationWide €";
        }

        else if ($data['amount'] < $averageTurnoverNationWide) {
            $nationalPosition = "Votre chiffre d'affaires déclaré est inférieur à la moyenne nationale qui est de $averageTurnoverNationWide €";
        }


        $averageTurnoverDepartment= $turnoverRepository->getAverageTurnoverInDepartment($departmentCode);
        $averageTurnoverDepartment = round($averageTurnoverDepartment, 2);

        //var_dump($averageTurnoverDepartment);
        //var_dump($departmentCode);
        //var_dump($departmentName);

        if ($data['amount'] > $averageTurnoverDepartment) {
            $departmentPosition = "Votre chiffre d'affaires déclaré est supérieur à la moyenne départementale ($departmentCode - $departmentName) qui est de $averageTurnoverDepartment €";
        }

        else if ($data['amount'] < $averageTurnoverDepartment) {
            $departmentPosition = "Votre chiffre d'affaires déclaré est inférieur à la moyenne départementale($departmentCode - $departmentName) qui est de $averageTurnoverDepartment €";
        }

        else {
            $departmentPosition = "Votre chiffre d'affaires déclaré est égal à la moyenne départementale ($departmentCode - $departmentName) qui est de $averageTurnoverDepartment €";
        }
        
        $averageTurnoverRegion= $turnoverRepository->getRegionalAverageTurnover($regionId);
        $averageTurnoverRegion = round($averageTurnoverRegion, 2);

        $regionPosition = "";

        if ($data['amount'] > $averageTurnoverRegion) {
            $regionPosition = "Votre chiffre d'affaires déclaré est supérieur à la moyenne régionale ($regionName) qui est de $averageTurnoverRegion €";
        }

        else if ($data['amount'] < $averageTurnoverRegion) {
            $regionPosition = "Votre chiffre d'affaires déclaré est inférieur à la moyenne régionale ($regionName) qui est de $averageTurnoverRegion €";
        }

        else {
            $regionPosition = "Votre chiffre d'affaires déclaré est égal à la moyenne régionale ($regionName) qui est de $averageTurnoverRegion €";
        }
    


        return new JsonResponse(['message' => 'Chiffre d\'affaires ajouté avec succès', "positionnement_national"=>$nationalPosition, "positionnement_departemental"=>$departmentPosition, "positionnement_regional"=>$regionPosition,"statistiques"=>[
            "moyenne_nationale"=>$averageTurnoverNationWide,
            "moyenne_departementale"=>$averageTurnoverDepartment,
            "moyenne_regionale"=>$averageTurnoverRegion
        
        ]], 201);
    }

    // /**************Récupération de tous les chiffres d'affaires d'un salon******************* */

    #[Route('/api/turnover/{salonId}', name: 'app_turnover_show', methods: ['GET'])]
    
    public function getTurnover(int $salonId, SalonRepository $salonRepository, TurnoverRepository $turnoverRepository): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non authentifié'], 401);
        }

        $salon = $salonRepository->getSalonById($salonId);

        if (!$salon) {
            return new JsonResponse(['message' => 'Salon non trouvé'], 404);
        }

        // Comparaison des ID des utilisateurs
        if ($salon->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['message' => 'Vous n\'êtes pas propriétaire de ce salon'], 403);
        }

        //$turnovers = $this->manager->getRepository(Turnover::class)->findBy(['salon_id' => $salonId]);

        $turnovers = $turnoverRepository->AllBySalonId($salonId);

        $data = [];

        foreach ($turnovers as $turnover) {
            $data[] = [
                'id' => $turnover->getId(),
                'period' => $turnover->getPeriod()->format('Y-m-d'),
                'turnover_amount' => $turnover->getTurnoverAmount(),
            ];
        }

        return new JsonResponse($data, 200);
    }



}
