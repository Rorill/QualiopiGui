<?php

namespace App\Entity;

use App\Repository\DocumentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentsRepository::class)]
class Documents
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $uploadedAT = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'Documents')]
    private Collection $instructor;

    #[ORM\ManyToOne(inversedBy: 'Docs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $Instructor = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formations $Formation = null;

    public function __construct()
    {
        $this->instructor = new ArrayCollection();
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

    public function getUploadedAT(): ?\DateTimeInterface
    {
        return $this->uploadedAT;
    }

    public function setUploadedAT(\DateTimeInterface $uploadedAT): static
    {
        $this->uploadedAT = $uploadedAT;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getInstructor(): Collection
    {
        return $this->instructor;
    }

    public function addInstructor(User $instructor): static
    {
        if (!$this->instructor->contains($instructor)) {
            $this->instructor->add($instructor);
            $instructor->setDocuments($this);
        }

        return $this;
    }

    public function removeInstructor(User $instructor): static
    {
        if ($this->instructor->removeElement($instructor)) {
            // set the owning side to null (unless already changed)
            if ($instructor->getDocuments() === $this) {
                $instructor->setDocuments(null);
            }
        }

        return $this;
    }

    public function setInstructor(?User $Instructor): static
    {
        $this->Instructor = $Instructor;

        return $this;
    }

    public function getFormation(): ?Formations
    {
        return $this->Formation;
    }

    public function setFormation(?Formations $Formation): static
    {
        $this->Formation = $Formation;

        return $this;
    }
}
