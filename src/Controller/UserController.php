<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\MailerService;
use App\Service\JsonResponseNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\PasswordValidatorService;
use App\Trait\StandardResponsesTrait;

class UserController extends AbstractController
{

    use StandardResponsesTrait;
    private EntityManagerInterface $manager;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private MailerService $mailerService;
    private Security $security;
    private JsonResponseNormalizer $jsonResponseNormalizer;

    private PasswordValidatorService $passwordValidatorService;

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, MailerService $mailerService, Security $security, JsonResponseNormalizer $jsonResponseNormalizer, PasswordValidatorService $passwordValidatorService)
    {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->mailerService = $mailerService;
        $this->security = $security;
        $this->jsonResponseNormalizer = $jsonResponseNormalizer;
        $this->passwordValidatorService = $passwordValidatorService;
    }



    /**************Enregistrement d'un nouvel utilisateur******************* */

/**
 * Cette méthode permet d'enregistrer un nouvel utilisateur
 * @param Request $request : la requête HTTP
 * @return JsonResponse : la réponse HTTP
 */

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $email = $data['email'];
        $plainPassword = $data['password'];

        // Vérification si l'email existe
        $emailExist = $this->userRepository->findOneBy(['email' => $email]);

      
         if ($emailExist) {
            
    return $this->respondEmailAlreadyExist();
        }

        // Vérification de la complexité du mot de passe et envoi d'une réponse d'erreur si le mot de passe n'est pas valide
     
        $passwordErrors = $this->passwordValidatorService->isPasswordComplex($plainPassword);

        if (!empty($passwordErrors)) {

            $InvalidPasswordResponse= $this->jsonResponseNormalizer->respondError('BAD_REQUEST', 'Le mot de passe n\'est pas valide . Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial. Critères non respectés : ' . implode(', ', $passwordErrors), 400);
            return $InvalidPasswordResponse;
      
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->generateEmailVerificationToken();

        // Hachage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->manager->persist($user);
        $this->manager->flush();

        //Envoi d'un email de confirmation de l'inscription

        

        $confirmationLink = $this->generateUrl('confirm_email', ['token' => $user->getEmailVerificationToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $emailBody = sprintf(
    '       Bonjour %s,<br><p>Votre inscription a été effectuée avec succès. Veuillez cliquer sur le lien suivant pour confirmer votre adresse email : <a href="%s">Confirmer votre adresse email</a></p>',
        $user->getFirstName(),
            $confirmationLink
);

            $this->mailerService->sendEmail(
            $user->getEmail(),
            'Inscription réussie',
            $emailBody
            
        );




    $registerSuccessResponse= $this->jsonResponseNormalizer->respondSuccess(200, ['message' =>'Utilisateur ajouté avec succès']);
    return $registerSuccessResponse;

    }

/**************Récupération des infos de l'utilisateur authentifié JWT******************* */

/**
 * Cette méthode permet de récupérer les informations de l'utilisateur authentifié
 * @return JsonResponse : la réponse HTTP
 * 
 */

    #[Route('api/profil/user/informations', name: 'get_user', methods: ['GET'])]

    public function getUserInfo():JsonResponse
    {
        $user = $this->security->getUser();

      if (!$user) {

            $userNotAuthenticatedResponse= $this->jsonResponseNormalizer->respondError('UNAUTHORIZED', 'Utilisateur non authentifié', 401);
            
            return $userNotAuthenticatedResponse;
          
        }

        $userInfoResponse= $this->jsonResponseNormalizer->respondSuccess(200, ['current_user' => [
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'email' => $user->getEmail(),
                    'emailVerified' => $user->isEmailVerified()
                ]]);
        return $userInfoResponse;


    }


   

/**
 * Cette méthode permet de modifier les données de l'utilisateur en cours
 *
 * @param Request $request
 * @return JsonResponse
 */

    #[Route('api/profil/user/update', name: 'update_user', methods: ['PATCH'])]

public function updateUser(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);


    $user = $this->security->getUser();

    if (!$user) {

     return $this->respondNotAuthenticated();
       
    }

        //Analyser les données reçues et vérifier que seuls les champs autorisés sont modifiés sinon retourner une erreur

        $invalidFields = array_diff(array_keys($data), ['firstName', 'lastName', 'email']);

        if (!empty($invalidFields)) {

            $invalidFieldsResponse= $this->jsonResponseNormalizer->respondError('BAD_REQUEST', 'Les champs suivants ne peuvent pas être modifiés : ' . implode(', ', $invalidFields), 400);
            return $invalidFieldsResponse;
            
        }



    if (isset($data['firstName'])) {
        $user->setFirstName($data['firstName']);
    }

    if (isset($data['lastName'])) {
        $user->setLastName($data['lastName']);
    }

//Procédure spéciale pour la modification de l'email

    if (isset($data['email'])) {
        
        //Génération d'un token de vérification d'email

        $user->setNewEmail($data['email']);
        $user->generateEmailChangeToken();

             

        //Envoi d'un email à l'utilisateur à son ancienne adresse pour confirmer si il est bien à l'origine de la demande de modification de l'email avec deux liens : un pour confirmer et un pour annuler

        $emailBody = sprintf(
            'Bonjour %s,<br><p>Vous avez demandé à modifier votre adresse email pour la nouvelle adresse %s. Veuillez cliquer sur le lien suivant pour confirmer votre nouvelle adresse email : <a href="%s">Confirmer votre adresse email</a></p><p>Si vous n\'êtes pas à l\'origine de cette demande, veuillez cliquer sur le lien suivant pour annuler la modification : <a href="%s">Annuler la modification</a></p>', $user->getFirstName(), $user->getNewEmail(),$this->generateUrl('app_confirm_email_change', ['token' => $user->getEmailChangeToken()], UrlGeneratorInterface::ABSOLUTE_URL), $this->generateUrl('app_cancel_email_change', ['token' => $user->getEmailChangeToken()], UrlGeneratorInterface::ABSOLUTE_URL));

        $this->mailerService->sendEmail(
            $user->getEmail(),
            'Modification de votre adresse email',
            $emailBody
        );

        $emailChangeMessage="Un email de confirmation a été envoyé à votre ancienne adresse email. Veuillez cliquer sur le lien qu'il contient pour confirmer la procédure de changement.";

        
      //  $confirmationLink = $this->generateUrl('confirm_email', ['token' => $user->getEmailVerificationToken()], UrlGeneratorInterface::ABSOLUTE_URL);

    }

    $this->manager->persist($user);
    $this->manager->flush();

    $updateSuccessResponse= $this->jsonResponseNormalizer->respondSuccess(200, ['message' => "Utilisateur modifié avec succès", 'emailChangeMessage' => $emailChangeMessage ?? null]);
    return $updateSuccessResponse;

}



}
