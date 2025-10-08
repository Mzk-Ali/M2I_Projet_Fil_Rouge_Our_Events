<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getEvents', 'event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre de l'événement est obligatoire")]
    #[Assert\Length(min:2, max: 255, minMessage: "Le titre de l'événement doit faire au moins {{ limit }} caractères", maxMessage: "Le titre de l'événement ne peut pas faire plus de {{ limit }} caractères")]
    #[Groups(['getEvents', 'event:read', 'event:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['event:read', 'event:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'url de l'image de l'évenement est obligatoire")]
    #[Assert\Url(message: "L'URL de l'image n'est pas valide")]
    #[Groups(['getEvents', 'event:read', 'event:write'])]
    private ?string $image_url = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La capacité est obligatoire")]
    #[Assert\Positive(message: "La capacité doit être un nombre positif")]
    #[Assert\LessThanOrEqual(value: 10000, message: "La capacité ne peut pas dépasser {{ compared_value }} personnes")]
    #[Groups(['event:read', 'event:write'])]
    private ?int $capacity = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La date de début de l'évenement est obligatoire")]
    #[Assert\Type(\DateTimeInterface::class, message: "La date de début de l'évenement doit être une date valide")]
    #[Assert\GreaterThan("today", message: "La date de début doit être dans le futur")]
    #[Groups(['getEvents', 'event:read', 'event:write'])]
    private ?\DateTime $start_datetime = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La date de fin de l'évenement est obligatoire")]
    #[Assert\Type(\DateTimeInterface::class, message: "La date de fin de l'évenement doit être une date valide")]
    #[Assert\Expression(
        "this.getEndDatetime() > this.getStartDatetime()",
        message: "La date de fin doit être postérieure à la date de début"
    )]
    #[Groups(['getEvents', 'event:read', 'event:write'])]
    private ?\DateTime $end_datetime = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'events')]
    #[Groups(['event:read', 'event:write'])]
    private Collection $categories;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event:read', 'event:write'])]
    private ?Premise $premise = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $manager = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'registeredEvents')]
    private Collection $registeredUsers;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->registeredUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(string $image_url): static
    {
        $this->image_url = $image_url;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getStartDatetime(): ?\DateTime
    {
        return $this->start_datetime;
    }

    public function setStartDatetime(\DateTime $start_datetime): static
    {
        $this->start_datetime = $start_datetime;

        return $this;
    }

    public function getEndDatetime(): ?\DateTime
    {
        return $this->end_datetime;
    }

    public function setEndDatetime(\DateTime $end_datetime): static
    {
        $this->end_datetime = $end_datetime;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getPremise(): ?Premise
    {
        return $this->premise;
    }

    public function setPremise(?Premise $premise): static
    {
        $this->premise = $premise;

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getRegisteredUsers(): Collection
    {
        return $this->registeredUsers;
    }

    public function addRegisteredUser(User $registeredUser): static
    {
        if (!$this->registeredUsers->contains($registeredUser)) {
            $this->registeredUsers->add($registeredUser);
            $registeredUser->addRegisteredEvent($this);
        }

        return $this;
    }

    public function removeRegisteredUser(User $registeredUser): static
    {
        if ($this->registeredUsers->removeElement($registeredUser)) {
            $registeredUser->removeRegisteredEvent($this);
        }

        return $this;
    }
}
