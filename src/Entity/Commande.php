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

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ticketPath = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: ElementCommande::class)]
    private Collection $elements;

    #[ORM\ManyToOne(targetEntity: CartePrepayee::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?CartePrepayee $cartePrepayee = null;

    public function __construct()
    {
        $this->elements = new ArrayCollection();
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getTicketPath(): ?string
    {
        return $this->ticketPath;
    }

    public function setTicketPath(?string $ticketPath): static
    {
        $this->ticketPath = $ticketPath;
        return $this;
    }

    public function getCartePrepayee(): ?CartePrepayee
    {
        return $this->cartePrepayee;
    }

    public function setCartePrepayee(?CartePrepayee $cartePrepayee): static
    {
        $this->cartePrepayee = $cartePrepayee;
        return $this;
    }

    /**
     * @return Collection<int, ElementCommande>
     */
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

    public function removeElement(ElementCommande $element): static
    {
        if ($this->elements->removeElement($element)) {
            if ($element->getCommande() === $this) {
                $element->setCommande(null);
            }
        }
        return $this;
    }
}