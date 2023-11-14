<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Users;

#[Route('/api')]
class UsersController extends AbstractController
{
    // registration of user
    #[Route('/register', methods: ['POST'])]
    public function index(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {     
        $em = $doctrine->getManager();
        $decoded = json_decode($request->getContent());
        $login = $decoded->login;
        $plaintextPassword = $decoded->password;
        $email = $decoded->email;
        $firstname = $decoded->firstname;
        $lastname = $decoded->lastname;
  
        $user = new Users();
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );

        $user->setLogin($login);
        $user->setPassword($hashedPassword);
        $user->setEmail($email);
        $user->setUsername($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);

        $em->persist($user);
        $em->flush();
  
        return $this->json(['message' => 'Registered Successfully'],200);
    }

    // display current user information
    #[Route('/users', methods: ['GET'])]
    public function show(Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $security->getUser();

        if ($user) {
            $login = $user->getLogin();
            $email = $user->getEmail();
            $firstname = $user->getFirstname();
            $lastname = $user->getLastname();

        } else {
            return json("user not found", 404);
        }

        $data_user = [
            "login"=>$login,
            "email"=>$email,
            "firstname"=>$firstname,
            "lastname"=>$lastname
        ];

        return $this->json($data_user);
    }

    // update current user information
    #[Route('/users', methods: ['PUT'])]
    public function update(ManagerRegistry $doctrine , Security $security, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $em = $doctrine->getManager();
        $user = $security->getUser();

        $decoded = json_decode($request->getContent());
        $login = $decoded->login;
        $plaintextPassword = $decoded->password;
        $email = $decoded->email;
        $firstname = $decoded->firstname;
        $lastname = $decoded->lastname;

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );

        $user->setLogin($login);    
        $user->setPassword($hashedPassword);
        $user->setEmail($email);
        $user->setUsername($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);

        $em->persist($user);
        $em->flush();

        $data_user = [
            "login"=>$user->getLogin(),
            "email"=>$user->getEmail(),
            "firstname"=>$user->getFirstname(),
            "lastname"=>$user->getLastname()
        ];

        return $this->json($data_user);
    }

    // logout and destroy token
    #[Route('/logout', name:'app_logout' , methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('Log out and destroy token of current user');
    }
}
