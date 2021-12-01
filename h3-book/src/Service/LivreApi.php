<?php

namespace App\Service;

use App\Entity\AgeRange;
use App\Entity\Book;
use App\Entity\Library;
use App\Entity\Status;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\BookRepository;
use App\Repository\TagRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class LivreApi
{

    /**
     * @var SerializerInterface
     */
    private $serializer;
    private $isbn;
    private $infosGoogle;
    private $entityManager;


    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager){
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * @param $isbn
     * @return array
     */
    public function getDataFromIsbn($isbn)
    {
        $this->isbn = $isbn;
        //$this->getGoogleIsbn(); // récupère les infos de google
        return $this->getGoogleIsbn();
    }

    private function getGoogleIsbn(){
        $response = @file_get_contents('https://www.googleapis.com/books/v1/volumes?q=isbn:' . $this->isbn) ?? null;
        if($response == false){
            $this->infosGoogle['erreur'] = "Google Books n'a aucune information";
            return null;
        }
        $article = $this->serializer->decode($response,'json');
        $this->infosGoogle = $article["items"][0]["volumeInfo"] ?? null;

        $keywords = '';
        $date = '';
        // transforme la date de publication de google en objet datetime
       if(!empty($this->infosGoogle['publishedDate'])){
           $this->infosGoogle['publishedDate'] = substr($this->infosGoogle['publishedDate'], 0, 10);
           //dd($this->infosGoogle['publishedDate']);
           $keywords = str_split($this->infosGoogle['publishedDate']) ?? '';
        }
        if($keywords != ''){
            //dd($keywords[4]);
            if(!empty($keywords[4])){
                $date = DateTime::createFromFormat('Y-m-j',$this->infosGoogle['publishedDate']);
                //dd($date);
            }else{
                $date = new DateTime($keywords[0].$keywords[1].$keywords[2].$keywords[3]."-01-01");
                //dd("test");
            }
            //dd($date);
        }
        $this->infosGoogle['publishedDate'] = $date;
        //dd($date);
        $images = array();
        if (!empty($this->infosGoogle['imageLinks']) && !empty($this->infosGoogle['imageLinks']['thumbnail'])) {
            array_push($images,$this->infosGoogle['imageLinks']['thumbnail']);
        }

        /*$book = $bookRepository->findAll();*/
        $book = new Book();
        $book
            ->setTitle($this->infosGoogle['title'])
            ->setSubtitle($this->infosGoogle['subtitle'] ?? '')
            ->setAuthor($this->infosGoogle['authors'][0])
            ->setEditor($this->infosGoogle['publisher'] ?? '')
            ->setPublishDate(new DateTime('2015-04-30 00:00:00'))
            ->setIsbn($this->isbn)
            ->setLanguage($this->infosGoogle['language'])
            ->setImage($this->infosGoogle['imageLinks']['thumbnail']  ?? '')
            ->setCategorie($this->infosGoogle['categories'][0] ?? '')
            ->setDescription($this->infosGoogle['description']?? '');

        $status = $this->entityManager->getRepository(Status::class)->findOneById(1);
        //dd($status);
        $status->addBook($book);
        //$book->setStatus($status);

        $age = $this->entityManager->getRepository(AgeRange::class)->findOneById(2);
        $age->addBook($book);
        //$book->setAgeRanges($age);

        $tag = $this->entityManager->getRepository(Tag::class)->findOneById(1);
        $tag2 = $this->entityManager->getRepository(Tag::class)->findOneById(93);
        //$tag3 = $this->entityManager->getRepository(Tag::class)->findOneById(85);
        //$tag->addBook($book);
        $book->addTag($tag);
        $book->addTag($tag2);
        //$book->addTag($tag3);

        //dd($book);
        $this->entityManager->persist($book);
        //dd($book);
        $this->entityManager->flush();

        //$this->infosGoogle['title']; // titre
        //$this->infosGoogle['subtitle']; // soustitre
        //$this->infosGoogle['authors'][0]; // auteurs
        //$this->infosGoogle['publisher']; // editeurs
        //$this->infosGoogle['publishedDate']; // date de publication
        //$this->isbn; // isbn
        //$this->infosGoogle['language']; // langage
        //$this->infosGoogle['imageLinks']['thumbnail']; // image
        //$this->infosGoogle['categories'][0]; // genre
        //$this->infosGoogle['description']; // description
        dd($book);
        return $this->isbn;
    }

    /*public function addBibliToUser($user){
        $bibli = new Library();
        dd($user);
        //$bibli->setUser($user->getId());
        //$this->entityManager->persist($bibli);
        //$this->entityManager->flush();
        return $bibli;
    }*/

    public function getBook($isbn){
        $book = $this->entityManager->getRepository(Book::class)->findOneByIsbn($isbn);
        return $book;
    }

    public function getBooks(){
        $books = $this->entityManager->getRepository(Book::class)->findAll();
        return $books;
    }

}