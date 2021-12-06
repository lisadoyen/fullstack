<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        if($this->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
            return $this->render('index.html.twig');
        } else {
            return $this->redirectToRoute('accueil');
        }
    }

    /**
     * @Route("/accueil", name="accueil")
     */
    public function accueil()
    {
        return $this->redirectToRoute('search');
    }

}
