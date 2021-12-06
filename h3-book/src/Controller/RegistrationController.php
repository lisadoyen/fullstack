<?php

namespace App\Controller;

use App\Entity\Library;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RegistrationController extends AbstractController
{


    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function register(Request $request,  UserPasswordHasherInterface $userPasswordHasher, SerializerInterface $serializer): Response
    {
        $params = json_decode($request->getContent(), true);
        if(!isset($params['lastName']) || empty($params['lastName'])){
            throw new HttpException(400, 'missing parameter');
        }

        if(!isset($params['firstName']) || empty($params['firstName'])){
            throw new HttpException(400, 'missing parameter');
        }

        if(!isset($params['password']) || empty($params['password'])){
            throw new HttpException(400, 'missing parameter');
        }


        if(!isset($params['email']) || empty($params['email'])){
            throw new HttpException(400, 'missing parameter');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $existingUser = $entityManager->getRepository(User::class)->findOneByEmail($params['email']);
        $results = [];
        if(null == $existingUser){

            $user = new User();
            $user->setRoles(['ROLE_USER']);
            $user->setCreationDate(new \DateTime('now'));
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $params['password']
                )
            );
            $user->setLastName($params['lastName']);
            $user->setFirstName($params['firstName']);
            $user->setEmail($params['email']);

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            $bibli = new Library();

            $returnArray = [
                'id' =>$user->getId(),
                'email'=>$user->getEmail(),
                'lastName'=>$user->getLastName(),
                'firstName'=>$user->getFirstName()
            ];

            $bibli->setUser($user);
            $entityManager->persist($bibli);
            $entityManager->flush();

            $results = $serializer->serialize(
                $returnArray,
                'json',
            );
        }
        return new JsonResponse($results, 200, [], true);
    }


    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function login(Request $request, UserPasswordHasherInterface $hasher, SerializerInterface $serializer): Response
    {
        $params = json_decode($request->getContent(), true);
        if(!isset($params['password']) || empty($params['password'])){
            throw new HttpException(400, 'missing parameter');
        }

        if(!isset($params['email']) || empty($params['email'])){
            throw new HttpException(400, 'missing parameter');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $existingUser = $entityManager->getRepository(User::class)->findOneByEmail($params['email']);

        if($existingUser == null) throw new HttpException(400, 'unknown email');
        else $user= $existingUser;

        if(!$hasher->isPasswordValid($user, $params['password'])){
            throw new HttpException(400, 'invalid password');
        }

        $returnArray = [
            'id' => $user->getId(),
            'password' => $user->getPassword(),
            'lastName' => $user->getlastName(),
            'firstName' => $user->getfirstName(),
            'email' => $user->getEmail()
        ];
        $resultats = $serializer->serialize(
            $returnArray,
            'json'
        );

        return new JsonResponse($resultats, 200, [], true);
    }


    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout() {
        return $this->render('index.html.twig');
    }
}
