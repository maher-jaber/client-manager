<?php

namespace App\Entity;

use App\Repository\ActionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
class Action
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    /**
     * @var Collection<int, Client>
     */
    #[ORM\ManyToMany(targetEntity: Client::class, inversedBy: 'actions')]
    private Collection $clients;

    /**
     * @var Collection<int, ClientActionLog>
     */
    #[ORM\ManyToMany(targetEntity: ClientActionLog::class, mappedBy: 'actions')]
    private Collection $clientActionLogs;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->clientActionLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

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
            $clientActionLog->addAction($this);
        }

        return $this;
    }

    public function removeClientActionLog(ClientActionLog $clientActionLog): static
    {
        if ($this->clientActionLogs->removeElement($clientActionLog)) {
            $clientActionLog->removeAction($this);
        }

        return $this;
    }
}
