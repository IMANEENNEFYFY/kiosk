<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $numeroCommande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column]
    private ?float $montantTotal = null;

    #[ORM\OneToMany(targetEntity: ElementCommande::class, mappedBy: 'commande', cascade: ['persist'])]
    private Collection $elements;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->numeroCommande = 'CMD-'.strtoupper(uniqid());
        $this->elements = new ArrayCollection();
    }

    // Ajoutez ces méthodes
    public function getElements(): Collection
    {
        return $this->elements;
    }

    public function addElement(ElementCommande $element): static
    {
        if (!$this->elements->contains($element)) {
            $this->elements->add($element);
            $element->setCommande($this);
        }
        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroCommande(): ?string
    {
        return $this->numeroCommande;
    }

    public function setNumeroCommande(string $numeroCommande): static
    {
        $this->numeroCommande = $numeroCommande;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getMontantTotal(): ?float
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(float $montantTotal): static
    {
        $this->montantTotal = $montantTotal;

        return $this;
    }
    // src/Entity/Commande.php
#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $ticketPath = null;

// Ajoutez les getter et setter
public function getTicketPath(): ?string
{
    return $this->ticketPath;
}

public function setTicketPath(?string $ticketPath): self
{
    $this->ticketPath = $ticketPath;
    return $this;
}

}