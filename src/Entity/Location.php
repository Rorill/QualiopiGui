<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    #[ORM\Column(length: 255)]
    private ?string $City = null;

    /**
     * @var Collection<int, Formations>
     */
    #[ORM\OneToMany(targetEntity: Formations::class, mappedBy: 'location')]
    private Collection $Formations;

    public function __construct()
    {
        $this->Formations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->City;
    }

    public function setCity(string $City): static
    {
        $this->City = $City;

        return $this;
    }

    /**
     * @return Collection<int, Formations>
     */
    public function getFormations(): Collection
    {
        return $this->Formations;
    }

    public function addFormation(Formations $formation): static
    {
        if (!$this->Formations->contains($formation)) {
            $this->Formations->add($formation);
            $formation->setLocation($this);
        }

        return $this;
    }

    public function removeFormation(Formations $formation): static
    {
        if ($this->Formations->removeElement($formation)) {
            // set the owning side to null (unless already changed)
            if ($formation->getLocation() === $this) {
                $formation->setLocation(null);
            }
        }

        return $this;
    }
}
