<?php

namespace App\Entity;

use App\Repository\FormationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationsRepository::class)]
class Formations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $site = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $starting_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $ending_date = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Documents>
     */
    #[ORM\OneToMany(targetEntity: Documents::class, mappedBy: 'Formation')]
    private Collection $documents;


    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'formations')]
    private Collection $Instructor;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->Instructor = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }
    public function setSite(string $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getSite(): ?string
    {
        return $this->site;
    }



    public function getStartingDate(): ?\DateTime
    {
        return $this->starting_date;
    }

    public function setStartingDate(\DateTime $starting_date): static
    {
        $this->starting_date = $starting_date;

        return $this;
    }

    public function getEndingDate(): ?\DateTime
    {
        return $this->ending_date;
    }

    public function setEndingDate(\DateTime $ending_date): static
    {
        $this->ending_date = $ending_date;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Documents>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Documents $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setFormation($this);
        }

        return $this;
    }

    public function removeDocument(Documents $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getFormation() === $this) {
                $document->setFormation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getInstructor(): Collection
    {
        return $this->Instructor;
    }

    public function addInstructor(User $instructor): static
    {
        if (!$this->Instructor->contains($instructor)) {
            $this->Instructor->add($instructor);
        }

        return $this;
    }

    public function removeInstructor(User $instructor): static
    {
        $this->Instructor->removeElement($instructor);

        return $this;
    }
}
