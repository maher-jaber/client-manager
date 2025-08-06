<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $commercial = null;

    #[ORM\Column(length: 255)]
    private ?string $nomClient;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateProposition = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroContrat = null;

    #[ORM\Column]
    private ?bool $refuse = null;

    #[ORM\Column]
    private ?bool $accordSigne = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $demarrageContrat = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $premiereFacturation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroClient = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephoneCabinet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gsmPraticien = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    /**
     * @var Collection<int, Action>
     */
    #[ORM\ManyToMany(targetEntity: Action::class, mappedBy: 'clients')]
    private Collection $actions;

    /**
     * @var Collection<int, ClientActionLog>
     */
    #[ORM\ManyToMany(targetEntity: ClientActionLog::class, mappedBy: 'clients')]
    private Collection $clientActionLogs;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
        $this->clientActionLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommercial(): ?string
    {
        return $this->commercial;
    }

    public function setCommercial(?string $commercial): static
    {
        $this->commercial = $commercial;

        return $this;
    }
    public function getNomClient(): ?string
    {
        return $this->nomClient;
    }

    public function setNomClient(string $nomClient): static
    {
        $this->nomClient = $nomClient;

        return $this;
    }
    public function getDateProposition(): ?\DateTime
    {
        return $this->dateProposition;
    }

    public function setDateProposition(?\DateTime $dateProposition): static
    {
        $this->dateProposition = $dateProposition;

        return $this;
    }

    public function getNumeroContrat(): ?string
    {
        return $this->numeroContrat;
    }

    public function setNumeroContrat(?string $numeroContrat): static
    {
        $this->numeroContrat = $numeroContrat;

        return $this;
    }

    public function isRefuse(): ?bool
    {
        return $this->refuse;
    }

    public function setRefuse(bool $refuse): static
    {
        $this->refuse = $refuse;

        return $this;
    }

    public function isAccordSigne(): ?bool
    {
        return $this->accordSigne;
    }

    public function setAccordSigne(bool $accordSigne): static
    {
        $this->accordSigne = $accordSigne;

        return $this;
    }

    public function getDemarrageContrat(): ?\DateTime
    {
        return $this->demarrageContrat;
    }

    public function setDemarrageContrat(?\DateTime $demarrageContrat): static
    {
        $this->demarrageContrat = $demarrageContrat;

        return $this;
    }

    public function getPremiereFacturation(): ?\DateTime
    {
        return $this->premiereFacturation;
    }

    public function setPremiereFacturation(?\DateTime $premiereFacturation): static
    {
        $this->premiereFacturation = $premiereFacturation;

        return $this;
    }

    public function getNumeroClient(): ?string
    {
        return $this->numeroClient;
    }

    public function setNumeroClient(?string $numeroClient): static
    {
        $this->numeroClient = $numeroClient;

        return $this;
    }

    public function getTelephoneCabinet(): ?string
    {
        return $this->telephoneCabinet;
    }

    public function setTelephoneCabinet(?string $telephoneCabinet): static
    {
        $this->telephoneCabinet = $telephoneCabinet;

        return $this;
    }

    public function getGsmPraticien(): ?string
    {
        return $this->gsmPraticien;
    }

    public function setGsmPraticien(?string $gsmPraticien): static
    {
        $this->gsmPraticien = $gsmPraticien;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Action>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(Action $action): static
    {
        if (!$this->actions->contains($action)) {
            $this->actions->add($action);
            $action->addClient($this);
        }

        return $this;
    }

    public function removeAction(Action $action): static
    {
        if ($this->actions->removeElement($action)) {
            $action->removeClient($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ClientActionLog>
     */
    public function getClientActionLogs(): Collection
    {
        return $this->clientActionLogs;
    }

    public function addClientActionLog(ClientActionLog $clientActionLog): static
    {
        if (!$this->clientActionLogs->contains($clientActionLog)) {
            $this->clientActionLogs->add($clientActionLog);
            $clientActionLog->addClient($this);
        }

        return $this;
    }

    public function removeClientActionLog(ClientActionLog $clientActionLog): static
    {
        if ($this->clientActionLogs->removeElement($clientActionLog)) {
            $clientActionLog->removeClient($this);
        }

        return $this;
    }
}
