<?php

namespace App\Entity;

use App\Repository\ClientActionLogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientActionLogRepository::class)]
class ClientActionLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $performedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $performedBy = null;

    /**
     * @var Collection<int, Client>
     */
    #[ORM\ManyToMany(targetEntity: Client::class, inversedBy: 'clientActionLogs')]
    private Collection $clients;

    /**
     * @var Collection<int, Action>
     */
    #[ORM\ManyToMany(targetEntity: Action::class, inversedBy: 'clientActionLogs')]
    private Collection $actions;

    #[ORM\Column(type: Types::TEXT, nullable:true)]
    private ?string $note = null;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->performedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerformedAt(): ?\DateTime
    {
        return $this->performedAt;
    }

    public function setPerformedAt(\DateTime $performedAt): static
    {
        $this->performedAt = $performedAt;

        return $this;
    }

    public function getPerformedBy(): ?string
    {
        return $this->performedBy;
    }

    public function setPerformedBy(string $performedBy): static
    {
        $this->performedBy = $performedBy;

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        $this->clients->removeElement($client);

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
        }

        return $this;
    }

    public function removeAction(Action $action): static
    {
        $this->actions->removeElement($action);

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

        return $this;
    }
}
