<?php

namespace App\Entity;

use App\Repository\SocietyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SocietyRepository::class)]
class Society
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
    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: 'entite')]
    private Collection $clients;

    /**
     * @var Collection<int, Action>
     */
    #[ORM\OneToMany(targetEntity: Action::class, mappedBy: 'entite')]
    private Collection $actions;

    /**
     * @var Collection<int, ClientActionLog>
     */
    #[ORM\OneToMany(targetEntity: ClientActionLog::class, mappedBy: 'entite')]
    private Collection $clientActionLogs;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'entite')]
    private Collection $users;

    /**
     * @var Collection<int, Permission>
     */
    #[ORM\OneToMany(targetEntity: Permission::class, mappedBy: 'Entreprise')]
    private Collection $permissions;

    

    public function __construct()
    {
        
        $this->clients = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->clientActionLogs = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->permissions = new ArrayCollection();
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
   
   public function __toString()
   {
    return $this->label;
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
            $client->setEntite($this);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getEntite() === $this) {
                $client->setEntite(null);
            }
        }

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
            $action->setEntite($this);
        }

        return $this;
    }

    public function removeAction(Action $action): static
    {
        if ($this->actions->removeElement($action)) {
            // set the owning side to null (unless already changed)
            if ($action->getEntite() === $this) {
                $action->setEntite(null);
            }
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
            $clientActionLog->setEntite($this);
        }

        return $this;
    }

    public function removeClientActionLog(ClientActionLog $clientActionLog): static
    {
        if ($this->clientActionLogs->removeElement($clientActionLog)) {
            // set the owning side to null (unless already changed)
            if ($clientActionLog->getEntite() === $this) {
                $clientActionLog->setEntite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addEntite($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeEntite($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): static
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
            $permission->setEntreprise($this);
        }

        return $this;
    }

    public function removePermission(Permission $permission): static
    {
        if ($this->permissions->removeElement($permission)) {
            // set the owning side to null (unless already changed)
            if ($permission->getEntreprise() === $this) {
                $permission->setEntreprise(null);
            }
        }

        return $this;
    }

 
}
