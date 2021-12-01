<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $legend;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberView;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberLike;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberComment;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberShare;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getLegend(): ?string
    {
        return $this->legend;
    }

    public function setLegend(string $legend): self
    {
        $this->legend = $legend;

        return $this;
    }

    public function getNumberView(): ?int
    {
        return $this->numberView;
    }

    public function setNumberView(int $numberView): self
    {
        $this->numberView = $numberView;

        return $this;
    }

    public function getNumberLike(): ?int
    {
        return $this->numberLike;
    }

    public function setNumberLike(int $numberLike): self
    {
        $this->numberLike = $numberLike;

        return $this;
    }

    public function getNumberComment(): ?int
    {
        return $this->numberComment;
    }

    public function setNumberComment(int $numberComment): self
    {
        $this->numberComment = $numberComment;

        return $this;
    }

    public function getNumberShare(): ?int
    {
        return $this->numberShare;
    }

    public function setNumberShare(int $numberShare): self
    {
        $this->numberShare = $numberShare;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): self
    {
        $this->users = $users;

        return $this;
    }

}
