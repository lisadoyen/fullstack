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
        return $this->getGoogleIsbn(); // récupère les infos de google
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
           $keywords = str_split($this->infosGoogle['publishedDate']) ?? '';
        }
        if($keywords != ''){
            if(!empty($keywords[4])){
                $date = DateTime::createFromFormat('Y-m-j',$this->infosGoogle['publishedDate']);
            }else{
                $date = new DateTime($keywords[0].$keywords[1].$keywords[2].$keywords[3]."-01-01");
            }
        }
        $this->infosGoogle['publishedDate'] = $date;
        $images = array();
        if (!empty($this->infosGoogle['imageLinks']) && !empty($this->infosGoogle['imageLinks']['thumbnail'])) {
            array_push($images,$this->infosGoogle['imageLinks']['thumbnail']);
        }
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

        /*$status = $this->entityManager->getRepository(Status::class)->findOneById(1);
        $status->addBook($book);
        $age = $this->entityManager->getRepository(AgeRange::class)->findOneById(2);
        $age->addBook($book);
        $tag = $this->entityManager->getRepository(Tag::class)->findOneById(1);
        $tag2 = $this->entityManager->getRepository(Tag::class)->findOneById(93);
        $book->addTag($tag);
        $book->addTag($tag2);*/
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        dd($book);
        return $this->isbn;
    }

    public function getBook($isbn){
        $book = $this->entityManager->getRepository(Book::class)->findOneByIsbn($isbn);
        return $book;
    }

    public function getBooks(){
        $books = $this->entityManager->getRepository(Book::class)->findAll();
        return $books;
    }

}