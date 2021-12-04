<?php

namespace App\Controller;

use App\Entity\Library;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_register")
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function register2(Request $request, UserPasswordHasherInterface $userPasswordHasher,
                             EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setRoles(['ROLE_USER']);
            $user->setCreationDate(new \DateTime('now'));
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email


            $bibli = new Library();
            $bibli->setUser($user);
            $entityManager->persist($bibli);
            $entityManager->flush();

            return $this->redirectToRoute('search');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @return Response
     * @throws \HttpException
     */
    public function register(Request $request,  UserPasswordHasherInterface $userPasswordHasher,
                          EntityManagerInterface $entityManager): Response
    {
        $params = json_decode($request->getContent(), true);
        var_dump($params);
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

            $bibli->setUser($user);
            $entityManager->persist($bibli);
            $entityManager->flush();
        }
        $returnArray = [
            'id' =>$user->getId(),
            'email'=>$user->getEmail(),
            'lastName'=>$user->getLastName(),
            'firstName'=>$user->getFirstName()
        ];
        return $this->json($returnArray);
    }


    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @return Response
     * @throws \HttpException
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
     * @Route("/connexion", name="security_login")
     * @param AuthenticationUtils $authenticationUtils
     * @param Request $request
     * @return Response
     * Affiche la page pour se connecter au site
     */
    public function login2(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }



    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout() {
        return $this->render('index.html.twig');
    }
}
