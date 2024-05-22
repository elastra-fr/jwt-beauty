<?php

namespace App\Controller;

use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Salon;
use DateTime;


class SalonController extends AbstractController
{

private $security;
private $manager;

    public function __construct(Security $security,  EntityManagerInterface $manager)
    {
        $this->security = $security;
        $this->manager = $manager;
    }


    /***************Liste des salons dont l'utilisateur actuelle est propriétaire**/
    #[Route('/api/profil/salons', name: 'app_salon', methods: ['GET'])]
    /*public function index(): Response
    {
        return $this->render('salon/index.html.twig', [
            'controller_name' => 'SalonController',
        ]);
    }*/

    public function getListSalon(): JsonResponse
    {
     
     $user = $this->security->getUser();

        $salons = $user->getSalons();
        



if ($salons->isEmpty()) {
        return new JsonResponse(['message' => 'Aucun salon trouvé pour cet utilisateur'], 200);
    }

    return new JsonResponse($salons, 200);

    }


    /**************Créer un salon pour l'utilisateur actuel en récupérant les données de la requete http*******************/

    #[Route('/api/profil/create-salon', name: 'app_create_salon', methods: ['POST'])]

    public function createSalon(Request $request):JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $salon_name = $data['salon_name'];
        $adress = $data['adress'];
        $city = $data['city'];
        $zipCode = $data['zipCode'];
        $department_code = $data['department_code'];
        $etp = $data['etp'];
         $opening_date = DateTime::createFromFormat('Y-m-d\TH:i:s', $data['opening_date']);

        $user = $this->security->getUser();
        $user_id = $user->getId();

        $salon = new Salon();
        $salon->setSalonName($salon_name);
        $salon->setAdress($adress);
        $salon->setCity($city);
        $salon->setZipCode($zipCode);
        $salon->setDepartmentCode($department_code);
        $salon->setEtp($etp);
        $salon->setOpeningDate($opening_date);
        $salon->setUserId($user_id);

        $this->manager->persist($salon);
        $this->manager->flush();

        return new JsonResponse(['message' => 'Salon créé avec succès'], 201);





    }









}
