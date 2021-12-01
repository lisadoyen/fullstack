<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Library;
use App\Service\LivreApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class LivreApiController extends AbstractController
{
    /**
     * @var LivreApi
     */
    private $livreApi;

    public function __construct(LivreApi $livreApi)
    {
        $this->livreApi = $livreApi;
    }

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
     * @Route ( "/showbook/{isbn}", name="show_book_by_isbn")
     * @param LivreApi $livreApi
     * @param $isbn
     * @return Response
     */
    public function showBookByIsbn(SerializerInterface $serializer, $isbn){
        $book = $this->getDoctrine()->getManager()->getRepository(Book::class)->findOneByIsbn($isbn);
        $resultats = $serializer->serialize(
            $book,
            'json',
            [AbstractNormalizer::ATTRIBUTES =>
                ['id', 'title', 'image', 'isbn'
                ]
            ]
        );
        // DATA, code_statut HTTP, tableau de contexte , json : true
        return new JsonResponse($resultats, 200, [], true);
    }



    /**
     * @Route ("showbook", name="showbook")
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function showBooks(SerializerInterface $serializer){

        $books = $this->getDoctrine()->getManager()
            ->getRepository(Book::class)
            ->findAll();

        $resultats = $serializer->serialize(
            $books,
            'json',
            [AbstractNormalizer::ATTRIBUTES =>
                ['id', 'title', 'image', 'isbn'
                ]
            ]
        );

        // DATA, code_statut HTTP, tableau de contexte , json : true
        return new JsonResponse($resultats, 200, [], true);
    }

    /**
     * @Route ("showlibrary", name="show_library")
     */
    public function showLibrary(){
        return $this->render('showLibrary.html.twig');
    }
}

