<?php

namespace App\Entity;

use App\Repository\PremiseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PremiseRepository::class)]
class Premise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    #[Assert\Length(min:2, max: 255, minMessage: "L'adresse doit faire au moins {{ limit }} caractères", maxMessage: "L'adresse ne peut pas faire plus de {{ limit }} caractères")]
    #[Groups(['event:read'])]
    private ?string $address = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "La ville est obligatoire")]
    #[Assert\Length(min:2, max: 100, minMessage: "La ville doit faire au moins {{ limit }} caractères", maxMessage: "La ville ne peut pas faire plus de {{ limit }} caractères")]
    #[Groups(['event:read'])]
    private ?string $city = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le code postal est obligatoire")]
    #[Assert\Length(min:2, max: 50, minMessage: "Le code postal doit faire au moins {{ limit }} caractères", maxMessage: "Le code postal ne peut pas faire plus de {{ limit }} caractères")]
    #[Groups(['event:read'])]
    private ?string $postal_code = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'premise')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setPostalCode(string $postal_code): static
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setPremise($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getPremise() === $this) {
                $event->setPremise(null);
            }
        }

        return $this;
    }
}
