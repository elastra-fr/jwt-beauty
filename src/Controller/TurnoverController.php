<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Turnover;
//use App\Repository\DepartementRepository;
use App\Repository\SalonRepository;
use App\Repository\TurnoverRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;
use App\Service\JsonResponseNormalizer;
use App\Trait\StandardResponsesTrait;

class TurnoverController extends AbstractController
{
    use StandardResponsesTrait;

    private Security $security;
    private EntityManagerInterface $manager;
    private JsonResponseNormalizer $jsonResponseNormalizer;

    /**
     * TurnoverController constructor.
     *
     * @param Security $security
     * @param EntityManagerInterface $manager
     * @param JsonResponseNormalizer $jsonResponseNormalizer
     */
    public function __construct(Security $security, EntityManagerInterface $manager, JsonResponseNormalizer $jsonResponseNormalizer)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
    }

    /**
     * Ajoute un chiffre d'affaires pour un salon
     * @param Request $request : la requête HTTP
     * @param SalonRepository $salonRepository : le repository des salons
     * @param TurnoverRepository $turnoverRepository : le repository des chiffres d'affaires
     * @return JsonResponse : la réponse HTTP
     */

    #[Route('/api/turnover-insert/{salonId}', name: 'app_turnover_add', methods: ['POST'])]
    public function addTurnover(Request $request, SalonRepository $salonRepository, TurnoverRepository $turnoverRepository): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->respondNotAuthenticated();
        }

        $salonId = $request->get('salonId');
        $salon = $salonRepository->getSalonById($salonId);

        if (!$salon) {
            return $this->respondSalonNotFound();
        }

        if ($salon->getUser()->getId() !== $user->getId()) {
            return $this->respondNotOwner();
        }

        $departement = $salon->getDepartement();
        $region = $departement->getRegion();

        $departmentCode = $departement->getCode();
        $departmentName = $departement->getName();

        $regionId = $region->getId();
        $regionName = $region->getRegionName();

        $lastMonthFirstDay = (new DateTime('first day of last month'))->setTime(0, 0);
        $turnover = $this->manager->getRepository(Turnover::class)->findOneBy([
            'salon_id' => $salonId,
            'period' => $lastMonthFirstDay,
        ]);

        if ($turnover) {
            return $this->respondAlreadyExists();
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
        } else if ($data['amount'] < $averageTurnoverNationWide) {
            $nationalPosition = "Votre chiffre d'affaires déclaré est inférieur à la moyenne nationale qui est de $averageTurnoverNationWide €";
        }

        $averageTurnoverDepartment = $turnoverRepository->getAverageTurnoverInDepartment($departmentCode);
        $averageTurnoverDepartment = round($averageTurnoverDepartment, 2);

        if ($data['amount'] > $averageTurnoverDepartment) {
            $departmentPosition = "Votre chiffre d'affaires déclaré est supérieur à la moyenne départementale ($departmentCode - $departmentName) qui est de $averageTurnoverDepartment €";
        } else if ($data['amount'] < $averageTurnoverDepartment) {
            $departmentPosition = "Votre chiffre d'affaires déclaré est inférieur à la moyenne départementale($departmentCode - $departmentName) qui est de $averageTurnoverDepartment €";
        } else {
            $departmentPosition = "Votre chiffre d'affaires déclaré est égal à la moyenne départementale ($departmentCode - $departmentName) qui est de $averageTurnoverDepartment €";
        }

        $averageTurnoverRegion = $turnoverRepository->getRegionalAverageTurnover($regionId);
        $averageTurnoverRegion = round($averageTurnoverRegion, 2);

        $regionPosition = "";

        if ($data['amount'] > $averageTurnoverRegion) {
            $regionPosition = "Votre chiffre d'affaires déclaré est supérieur à la moyenne régionale ($regionName) qui est de $averageTurnoverRegion €";
        } else if ($data['amount'] < $averageTurnoverRegion) {
            $regionPosition = "Votre chiffre d'affaires déclaré est inférieur à la moyenne régionale ($regionName) qui est de $averageTurnoverRegion €";
        } else {
            $regionPosition = "Votre chiffre d'affaires déclaré est égal à la moyenne régionale ($regionName) qui est de $averageTurnoverRegion €";
        }

        $successResponse = $this->jsonResponseNormalizer->respondSuccess(201, [
            'message' => 'Chiffre d\'affaires ajouté avec succès',
            "positionnement_national" => $nationalPosition,
            "positionnement_departemental" => $departmentPosition,
            "positionnement_regional" => $regionPosition,
            "statistiques" => [
                "moyenne_nationale" => $averageTurnoverNationWide,
                "moyenne_departementale" => $averageTurnoverDepartment,
                "moyenne_regionale" => $averageTurnoverRegion
            ]
        ]);

        return $successResponse;
    }

    /**
     * Récupère l'historique des chiffres d'affaires pour un salon
     * @param int $salonId : l'identifiant du salon
     * @param SalonRepository $salonRepository : le repository des salons
     * @param TurnoverRepository $turnoverRepository : le repository des chiffres d'affaires
     * @return JsonResponse : la réponse HTTP
     * 
     */

    #[Route('/api/turnover/{salonId}', name: 'app_turnover_show', methods: ['GET'])]
    public function getTurnover(int $salonId, SalonRepository $salonRepository, TurnoverRepository $turnoverRepository): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->respondNotAuthenticated();
        }

        $salon = $salonRepository->getSalonById($salonId);

        if (!$salon) {
            return $this->respondSalonNotFound();
        }

        if ($salon->getUser()->getId() !== $user->getId()) {
            return $this->respondNotOwner();
        }

        $turnovers = $turnoverRepository->AllBySalonId($salonId);

        $data = [];

        foreach ($turnovers as $turnover) {
            $data[] = [
                'id' => $turnover->getId(),
                'period' => $turnover->getPeriod()->format('Y-m-d'),
                'turnover_amount' => $turnover->getTurnoverAmount(),
            ];
        }

        $tunoverHistory = $this->jsonResponseNormalizer->respondSuccess(200, $data);
        return $tunoverHistory;
    }
}
