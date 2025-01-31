<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $texte = null;

    #[ORM\Column]
    private ?bool $publie = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'article_id')]
    private Collection $comm;

    public function __construct()
    {
        $this->comm = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }

    public function isPublie(): ?bool
    {
        return $this->publie;
    }

    public function setPublie(bool $publie): static
    {
        $this->publie = $publie;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getComm(): Collection
    {
        return $this->comm;
    }

    public function addComm(Commentaire $comm): static
    {
        if (!$this->comm->contains($comm)) {
            $this->comm->add($comm);
            $comm->setArticleId($this);
        }

        return $this;
    }

    public function removeComm(Commentaire $comm): static
    {
        if ($this->comm->removeElement($comm)) {
            // set the owning side to null (unless already changed)
            if ($comm->getArticleId() === $this) {
                $comm->setArticleId(null);
            }
        }

        return $this;
    }
}
