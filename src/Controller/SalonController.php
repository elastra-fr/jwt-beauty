<?php

namespace App\Controller;

//use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Salon;
use App\Repository\DepartementRepository;
use DateTime;
use App\Repository\SalonRepository;
use App\Service\JsonResponseNormalizer;


class SalonController extends AbstractController
{

private Security $security;
private EntityManagerInterface $manager;

private JsonResponseNormalizer $jsonResponseNormalizer;

/**
 * SalonController constructor.
 */

    public function __construct(Security $security,  EntityManagerInterface $manager, JsonResponseNormalizer $jsonResponseNormalizer)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
    }


    /***************Liste des salons dont l'utilisateur actuelle est propriétaire**/
 
 /**
  * Cette méthode permet de récupérer la liste des salons dont l'utilisateur actuel est propriétaire
    * @return JsonResponse : la réponse HTTP
  */
 
    #[Route('/api/profil/salons', name: 'app_salon', methods: ['GET'])]


public function getListSalon(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            $UserNotAuthenticated = $this->jsonResponseNormalizer->respondError('UNAUTHORIZED', 'Utilisateur non authentifié', 401);
            return $UserNotAuthenticated;
            //return new JsonResponse(['message' => 'Utilisateur non authentifié'], 401);
        }

        $salons = $user->getSalons();

        if ($salons->isEmpty()) {
            $NoSalonFound = $this->jsonResponseNormalizer->respondError('NOT_FOUND', 'Aucun salon trouvé pour cet utilisateur', 200);
            return $NoSalonFound;
            //return new JsonResponse(['message' => 'Aucun salon trouvé pour cet utilisateur'], 200);
        }

        $salonArray = [];
        foreach ($salons as $salon) {

            $departement = $salon->getDepartement();
            $region = $departement ? $departement->getRegion() : null;


            $salonArray[] = [
                'id' => $salon->getId(),
                'salonName' => $salon->getSalonName(),
                'adress' => $salon->getAdress(),
                'city' => $salon->getCity(),
                'zipCode' => $salon->getZipCode(),
                'departmentName' => $departement ? $departement->getName() : null,
                'departmentCode' => $departement ? $departement->getCode() : null,
                'regionName' => $region ? $region->getRegionName() : null,
                //'regionCode' => $region ? $region->getCode() : null,          
                'etp' => $salon->getEtp(),
                'openingDate' => $salon->getOpeningDate()->format('Y-m-d H:i:s'),
            ];
        }

        $listSalon = $this->jsonResponseNormalizer->respondSuccess(200, ['salons' => $salonArray]);
        return $listSalon;
        //return new JsonResponse($salonArray, 200);
    }

    /**************Créer un salon pour l'utilisateur actuel en récupérant les données de la requete http*******************/

    #[Route('/api/profil/create-salon', name: 'app_create_salon', methods: ['POST'])]

    public function createSalon(Request $request, DepartementRepository $departementRepository):JsonResponse
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

        $departement = $departementRepository->findOneBy(['code' => $department_code]);
        
        //$user_id = $user->getId();

        $salon = new Salon();
        $salon->setSalonName($salon_name);
        $salon->setAdress($adress);
        $salon->setCity($city);
        $salon->setZipCode($zipCode);
        $salon->setDepartement($departement);
        $salon->setEtp($etp);
        $salon->setOpeningDate($opening_date);
        $salon->setUser($user);

        $this->manager->persist($salon);
        $this->manager->flush();

        return new JsonResponse(['message' => 'Salon créé avec succès'], 201);





    }

    /**************Récupération d'un salon  par son ID******************* */

    
    #[Route('/api/profil/salon/{id}', name: 'app_salon_show', methods: ['GET'])]
    public function getSalon(int $id, SalonRepository $salonRepository): JsonResponse
    {
        $salon = $salonRepository->getSalonById($id);

        if ($salon === null) {
            return new JsonResponse(['message' => 'Salon non trouvé'], 404);
        }

        $departement = $salon->getDepartement();
        $region = $departement ? $departement->getRegion() : null;

        $user = $this->security->getUser();

        if ($salon->getUser() !== $user) {
            return new JsonResponse(['message' => 'Accès interdit'], 403);
        }

        return new JsonResponse([
            'id' => $salon->getId(),
            'salonName' => $salon->getSalonName(),
            'adress' => $salon->getAdress(),
            'city' => $salon->getCity(),
            'zipCode' => $salon->getZipCode(),
            'departmentName' => $departement ? $departement->getName() : null,
            'departmentCode' => $departement ? $departement->getCode() : null,
            'regionName' => $region ? $region->getRegionName() : null,
            'etp' => $salon->getEtp(),
            'openingDate' => $salon->getOpeningDate()->format('Y-m-d H:i:s'),
        ], 200);
    }


    /**************Mettre à jour un salon par son ID******************* */

    #[Route('/api/profil/salon/update/{id}', name: 'app_salon_update', methods: ['PATCH'])]

    public function updateSalon(int $id, Request $request, SalonRepository $salonRepository, DepartementRepository $departementRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $salon = $salonRepository->getSalonById($id);

        if ($salon === null) {
            return new JsonResponse(['message' => 'Salon non trouvé'], 404);
        }

        $user = $this->security->getUser();

        $departement= $departementRepository->findOneBy(['code' => $data['department_code']]);

        if ($salon->getUser() !== $user) {
            return new JsonResponse(['message' => 'Accès interdit'], 403);
        }

      /*  $salon_name = $data['salon_name'];
        $adress = $data['adress'];
        $city = $data['city'];
        $zipCode = $data['zipCode'];
        $department_code = $data['department_code'];
        $etp = $data['etp'];
        $opening_date = DateTime::createFromFormat('Y-m-d\TH:i:s', $data['opening_date']);*/

        if (isset($data['salon_name'])) {
            $salon->setSalonName($data['salon_name']);
        }
        if (isset($data['adress'])) {
            $salon->setAdress($data['adress']);
        }
        if (isset($data['city'])) {
            $salon->setCity($data['city']);
        }
        if (isset($data['zipCode'])) {
            $salon->setZipCode($data['zipCode']);
        }
        if (isset($data['department_code'])) {
          $salon->setDepartement($departement);
        }
        if (isset($data['etp'])) {
            $salon->setEtp($data['etp']);
        }
        if (isset($data['opening_date'])) {
            $salon->setOpeningDate($data['opening_date']);
        }

        $this->manager->persist($salon);
        $this->manager->flush();

        return new JsonResponse(['message' => 'Salon mis à jour avec succès'], 200);
    }

    /**************Supprimer un salon par son ID******************* */  
/*
    #[Route('/api/profil/salon/delete/{id}', name: 'app_salon_delete', methods: ['DELETE'])]

    public function deleteSalon(int $id, SalonRepository $salonRepository): JsonResponse
    {
        $salon = $salonRepository->getSalonById($id);

        if ($salon === null) {
            return new JsonResponse(['message' => 'Salon non trouvé'], 404);
        }

        $user = $this->security->getUser();

        if ($salon->getUser() !== $user) {
            return new JsonResponse(['message' => 'Accès interdit'], 403);
        }

        $this->manager->remove($salon);
        $this->manager->flush();

        return new JsonResponse(['message' => 'Salon supprimé avec succès'], 200);
    }
*/

}
