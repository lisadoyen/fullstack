<?php

namespace App\Controller;

use App\Entity\Library;
use App\Service\LivreApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class LivreApiController extends AbstractController
{

    /**
     * @Route ("/search", name="search")
     * @param Request $request
     * @return Response
     */
    public function search(Request $request){

        if($request->isMethod('post')){
            $isbn = $_POST['isbn'];
            return $this->redirectToRoute('show_book_isbn',['isbn' => $isbn]);
        }
        return $this->render('search.html.twig');
    }

    /**
     * @Route ( "/show/{isbn}", name="show_book_isbn")
     * @param LivreApi $livreApi
     * @param $isbn
     * @return Response
     */
    public function getInfos(LivreApi $livreApi, $isbn){
        $book = $livreApi->getBook($isbn);
        return $this->render('show.html.twig', ['book' => $book]);
    }


    /**
     * @Route ("showlibrary", name="show_library")
     */
    public function showLibrary(){
        return $this->render('showLibrary.html.twig');
    }
}

