<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Tag;
use App\Repository\BookRepository;
use App\Repository\LibraryRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Service\LivreApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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
        $tags = [];
        foreach($book->getTags() as $tag){
            $tab_tag = [];
            $tab_tag['id'] = $tag->getId();
            $tab_tag['wording'] = $tag->getWording();
            array_push($tags, $tab_tag);
        }

        $returnArray = [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'subtitle' => $book->getSubtitle(),
            'isbn' => $book->getIsbn(),
            'image' => $book->getImage(),
            'editor' => $book->getEditor(),
            'author' => $book->getAuthor(),
            'publishDate' => $book->getPublishDate()->format('d/m/Y'),
            'description' => $book->getDescription(),
            'language' => $book->getLanguage(),
            'categorie' => $book->getCategorie(),
            'ageRange' => $book->getAgeRanges()->getWording(),
            'tags' =>  $tags
        ];

        $resultats = $serializer->serialize(
            $returnArray,
            'json'
        );
        // DATA, code_statut HTTP, tableau de contexte , json : true
        return new JsonResponse($resultats, 200, [], true);
    }

    /**
     * @Route ( "/addbook/{userId}/{id}", name="add_book_library")
     * @return Response
     */
    public function addBookToLibrary($id,SerializerInterface $serializer, LibraryRepository $libraryRepository,
        BookRepository $bookRepository, StatusRepository $statusRepository, $userId, UserRepository $userRepository){

        $book = $bookRepository->findOneById($id);
        $user = $userRepository->findOneById($userId);
        $library = $user->getLibrary();
        //$library = $libraryRepository->findOneById('5');
        $library->addBook($book);
        $status =  $statusRepository->findOneByWording('je veux');
        $book->setStatus($status);
        // changer statut => je veux/j'ai lu etc
        $this->getDoctrine()->getManager()->persist($library);
        $this->getDoctrine()->getManager()->persist($book);
        $this->getDoctrine()->getManager()->flush();

        $resultats = $serializer->serialize(
            $library,
            'json',
            [AbstractNormalizer::ATTRIBUTES =>
                ['id', 'title', 'image', 'isbn', 'editor', 'author', 'publish_date', 'description',
                    'language', 'categorie'
                ]
            ]
        );

        // DATA, code_statut HTTP, tableau de contexte , json : true
        return new JsonResponse($resultats, 200, [], true);
    }

    /**
     * @Route ( "/showlibrary/{userId}", name="show_library_user")
     * @param $userId
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function showLibrary($userId, SerializerInterface $serializer, UserRepository $userRepository){
        $user = $userRepository->findOneById($userId);
        $library = $user->getLibrary();

        $books = [];
        foreach($library->getBooks() as $book){
            $tab_book = [];
            $tab_book['id'] = $book->getId();
            $tab_book['image'] = $book->getImage();
            $tab_book['isbn'] = $book->getIsbn();
            $tab_book['title'] = $book->getTitle();
            array_push($books, $tab_book);
        }
        $returnArray = [
            'id' => $library->getId(),
            'books' => $books
        ];

        $resultats = $serializer->serialize(
            $returnArray,
            'json',
        );

        // DATA, code_statut HTTP, tableau de contexte , json : true
        return new JsonResponse($resultats, 200, [], true);
    }

    /**
     * @Route ( "/verify/{userId}/{bookId}", name="add_book_verify")
     * @param $userId
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function verifyBookInLib($userId, $bookId, UserRepository $userRepository,
                                    SerializerInterface $serializer, BookRepository $bookRepository){

        $user = $userRepository->findOneById($userId);
        $library = $user->getLibrary();
        $book = $bookRepository->findOneById($bookId);
        if($library->getBooks()->contains($book)){
            $resultats = $serializer->serialize(
                true,
                'json',
            );
            return new JsonResponse($resultats, 200, [], true);
        }else{
            $resultats = $serializer->serialize(
                false,
                'json',
            );
            return new JsonResponse($resultats, 200, [], true);
        }

    }

    /** @Route( "/getuser/{userId}", name="get_userId")
     *
     */
    public function getUserId($userId, UserRepository $userRepository, SerializerInterface $serializer){
        $user = $userRepository->findOneById($userId);
        $resultats = $serializer->serialize(
            $user->getId(),
            'json',
        );
        return new JsonResponse($resultats, 200, [], true);
    }
    /**
     * @Route ( "/recommendation/{userId}/{bookId}", name="add_book_verify")
     * @param $userId
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function recommendationProfil($userId, $bookId, BookRepository $bookRepository, UserRepository $userRepository,
                                         LibraryRepository $libraryRepository, SerializerInterface $serializer){
        $user = $userRepository->findOneById($userId);
        $library = $user->getLibrary();
        $book = $bookRepository->findOneById($bookId);

        $libraries = $libraryRepository->findAll();

        $users = [];
        foreach($libraries as $librarie){
            $userTemp = [];
            $libUser = $librarie->getUser();
            if($librarie->getBooks()->contains($book)){
                if($libUser != $user) {
                    $userTemp['id'] = $libUser->getId();
                    $userTemp['lastName'] = $libUser->getLastName();
                    $userTemp['firstName'] = $libUser->getFirstName();
                    $userTemp['picture'] = $libUser->getPicture();
                    array_push($users, $userTemp);
                }
            }
        }

        $resultats = $serializer->serialize(
            $users,
            'json',
        );
        return new JsonResponse($resultats, 200, [], true);

    }

    /**
     * @Route ( "/recommendationTag/{bookId}", name="recom_tag")
     * @param $userId
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function recommendationTag($bookId, BookRepository $bookRepository, SerializerInterface $serializer){

        $book = $bookRepository->findOneBy(['id' => $bookId]);

        $tags = $book->getTags();

        $books = [];
        foreach ($tags as $tag) {
            foreach ($tag->getBooks() as $tagBook) {
                $tempBook = [];
                $tempBook['id'] = $tagBook->getId();
                $tempBook['title'] = $tagBook->getTitle();
                $tempBook['image'] = $tagBook->getImage();
                $tempBook['isbn'] = $tagBook->getIsbn();
                $tempBook['categorie'] = $tagBook->getCategorie();
                $tempBook['editor'] = $tagBook->getEditor();

                $temptags = [];
                foreach ($tagBook->getTags() as $tagBookTag) {
                    $tempTag = [];
                    $tempTag['id'] = $tagBookTag->getId();
                    $tempTag['wording'] = $tagBookTag->getWording();
                    array_push($temptags, $tempTag);
                }
                $tempBook['tags'] = $temptags;
                if(!(in_array($tempBook,$books)) and $tempBook['id'] != $book->getId()) array_push($books, $tempBook);
            }
        }
        $resultats = $serializer->serialize(
            $books,
            'json',
        );
        return new JsonResponse($resultats, 200, [], true);

    }

    /**
     * @Route ( "/showtags", name="show_tags")
     * @return Response
     */
    public function showTags(SerializerInterface $serializer){
       $tags = $this->getDoctrine()->getManager()->getRepository(Tag::class)->findBy([], ['wording' => 'ASC'],10);

        $resultats = $serializer->serialize(
            $tags,
            'json',
            [AbstractNormalizer::ATTRIBUTES =>
                ['id', 'wording'
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
                ['id', 'title', 'image', 'isbn', 'editor', 'author', 'description',
                    'language', 'categorie', 'publishDate'
                ]
            ]
        );

        // DATA, code_statut HTTP, tableau de contexte , json : true
        return new JsonResponse($resultats, 200, [], true);
    }

    /**
     * @Route ("showlibrary", name="show_library")
     */
    public function showLibrary2(){
        return $this->render('showLibrary.html.twig');
    }
}

