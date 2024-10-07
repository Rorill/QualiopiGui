<?php

namespace App\Entity;

use App\Repository\DocumentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
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

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formations $Formation = null;

    #[ORM\ManyToOne(inversedBy: 'Document')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $Formateur = null;


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

    public function getFormation(): ?Formations
    {
        return $this->Formation;
    }

    public function setFormation(?Formations $Formation): static
    {
        $this->Formation = $Formation;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getFormateur(): ?User
    {
        return $this->Formateur;
    }

    public function setFormateur(?User $Formateur): static
    {
        $this->Formateur = $Formateur;

        return $this;
    }

    public function __construct()
    {
        $this->uploadedAT = new \DateTimeImmutable;
    }






}
