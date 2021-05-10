<?php

namespace App\Entity;

use App\Repository\TrainingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrainingRepository::class)
 */
class Training
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $trainingDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $slot;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $info;

    /**
     * @ORM\Column(type="datetime")
     */
    private $openingRegistrationDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $adult;

    /**
     * @ORM\OneToMany(targetEntity=UserTraining::class, mappedBy="training")
     */
    private $userTrainings;

    public function __construct()
    {
        $this->userTrainings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrainingDate(): ?\DateTimeInterface
    {
        return $this->trainingDate;
    }

    public function setTrainingDate(\DateTimeInterface $trainingDate): self
    {
        $this->trainingDate = $trainingDate;

        return $this;
    }

    public function getSlot(): ?int
    {
        return $this->slot;
    }

    public function setSlot(int $slot): self
    {
        $this->slot = $slot;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getOpeningRegistrationDate(): ?\DateTimeInterface
    {
        return $this->openingRegistrationDate;
    }

    public function setOpeningRegistrationDate(\DateTimeInterface $openingRegistrationDate): self
    {
        $this->openingRegistrationDate = $openingRegistrationDate;

        return $this;
    }

    public function getAdult(): ?bool
    {
        return $this->adult;
    }

    public function setAdult(bool $adult): self
    {
        $this->adult = $adult;

        return $this;
    }

    /**
     * @return Collection|UserTraining[]
     */
    public function getUserTrainings(): Collection
    {
        return $this->userTrainings;
    }

    public function addUserTraining(UserTraining $userTraining): self
    {
        if (!$this->userTrainings->contains($userTraining)) {
            $this->userTrainings[] = $userTraining;
            $userTraining->setTraining($this);
        }

        return $this;
    }

    public function removeUserTraining(UserTraining $userTraining): self
    {
        if ($this->userTrainings->removeElement($userTraining)) {
            // set the owning side to null (unless already changed)
            if ($userTraining->getTraining() === $this) {
                $userTraining->setTraining(null);
            }
        }

        return $this;
    }

}
