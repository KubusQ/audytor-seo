<?php

namespace App\Entity;

use App\Repository\AuditsDataRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AuditsDataRepository::class)]
class AuditsData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $uid = null;

    #[ORM\Column(length: 255)]
    private ?string $domain = null;

    #[ORM\Column]
    private array $data = [];

    #[ORM\ManyToOne(inversedBy: 'auditsdatas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUID(): ?Uuid
    {
        return $this->uid;
    }

    public function setUID(Uuid $UID): static
    {
        $this->uid = $UID;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
