<?php

namespace App\Entity;

use App\Repository\TurnoverRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TurnoverRepository::class)]
class Turnover
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $period = null;

    #[ORM\Column(name: "salon_id", type: Types::INTEGER)]
    private ?int $salon_id = null;

    #[ORM\ManyToOne(targetEntity: Salon::class)]
    #[ORM\JoinColumn(name: "salon_id", referencedColumnName: "id", nullable: false)]
    private ?Salon $salon = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $turnoverAmount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getPeriod(): ?\DateTimeInterface
    {
        return $this->period;
    }

    public function setPeriod(\DateTimeInterface $period): static
    {
        $this->period = $period;

        return $this;
    }

    public function getSalonId(): ?int
    {
        return $this->salon_id;
    }

    public function setSalonId(int $salon_id): static
    {
        $this->salon_id = $salon_id;

        return $this;
    }

    public function getSalon(): ?Salon
    {
        return $this->salon;
    }

    public function setSalon(?Salon $salon): static
    {
        $this->salon = $salon;

        return $this;
    }

    public function getTurnoverAmount(): ?string
    {
        return $this->turnoverAmount;
    }

    public function setTurnoverAmount(string $turnoverAmount): static
    {
        $this->turnoverAmount = $turnoverAmount;

        return $this;
    }
}
