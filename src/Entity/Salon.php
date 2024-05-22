<?php

namespace App\Entity;

use App\Repository\SalonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalonRepository::class)]
class Salon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $user_id = null;

    #[ORM\Column(length: 255)]
    private ?string $salon_name = null;

    #[ORM\Column(length: 255)]
    private ?string $adress = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    private ?string $zipCode = null;

    #[ORM\Column(length: 255)]
    private ?string $department_code = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $etp = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $opening_date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getSalonName(): ?string
    {
        return $this->salon_name;
    }

    public function setSalonName(string $salon_name): static
    {
        $this->salon_name = $salon_name;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): static
    {
        $this->adress = $adress;

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

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getDepartmentCode(): ?string
    {
        return $this->department_code;
    }

    public function setDepartmentCode(string $department_code): static
    {
        $this->department_code = $department_code;

        return $this;
    }

    public function getEtp(): ?string
    {
        return $this->etp;
    }

    public function setEtp(string $etp): static
    {
        $this->etp = $etp;

        return $this;
    }

    public function getOpeningDate(): ?\DateTimeInterface
    {
        return $this->opening_date;
    }

    public function setOpeningDate(\DateTimeInterface $opening_date): static
    {
        $this->opening_date = $opening_date;

        return $this;
    }
}
