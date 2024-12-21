<?php

namespace App\Entity;

use App\Repository\DetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetteRepository::class)]
class Dette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column]
    private ?float $montantVerser = null;

    #[ORM\Column]
    private ?float $montantRestant = null;

    #[ORM\ManyToOne(inversedBy: 'dettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\OneToMany(mappedBy: 'dette', targetEntity: Paiement::class, cascade: ['persist', 'remove'])]
    private Collection $paiement;

    #[ORM\ManyToMany(targetEntity: Article::class)]
    private Collection $articles;

    public function __construct()
    {
        $this->paiement = new ArrayCollection();
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getMontantVerser(): ?float
    {
        return $this->montantVerser;
    }

    public function setMontantVerser(float $montantVerser): static
    {
        $this->montantVerser = $montantVerser;

        return $this;
    }

    public function setMontantRestant(float $montantRestant): static
    {
        // Assurez-vous que le montant restant soit au minimum 0
        $this->montantRestant = max(0, $montantRestant);
    
        return $this;
    }
    
    public function getMontantRestant(): ?float
    {
        // Si le montant restant est négatif, retourne 0
        return $this->montantRestant < 0 ? 0 : $this->montantRestant;
    }
    
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'en_cours'])]
private ?string $etat = 'en_cours';

public function getEtat(): ?string
{
    return $this->etat;
}

public function setEtat(string $etat): self
{
    $this->etat = $etat;
    return $this;
}


    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiement(): Collection
    {
        return $this->paiement;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiement->contains($paiement)) {
            $this->paiement->add($paiement);
            $paiement->setDette($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): static
    {
        if ($this->paiement->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getDette() === $this) {
                $paiement->setDette(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        $this->articles->removeElement($article);

        return $this;
    }
}
