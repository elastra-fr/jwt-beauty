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

class UserController extends AbstractController
{
    private $manager;
    private $userRepository;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

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
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Cet email existe déjà'
                ],
            );
        }

        // Vérification de la complexité du mot de passe
        if (!$this->isPasswordComplex($plainPassword)) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.'
                ],
            );
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        // Hachage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            [
                'status' => true,
                'message' => 'Utilisateur ajouté avec succès'
            ],
        );
    }

    private function isPasswordComplex(string $password): bool
    {
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasLowercase = preg_match('/[a-z]/', $password);
        $hasNumber = preg_match('/[0-9]/', $password);
        $hasSpecialChar = preg_match('/[\W]/', $password);
        $hasMinLength = strlen($password) >= 8;

        return $hasUppercase && $hasLowercase && $hasNumber && $hasSpecialChar && $hasMinLength;
    }
}
