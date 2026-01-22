<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Workflow>
     */
    #[ORM\OneToMany(targetEntity: Workflow::class, mappedBy: 'product')]
    private Collection $workflows;

    public function __construct()
    {
        $this->workflows = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Workflow>
     */
    public function getWorkflows(): Collection
    {
        return $this->workflows;
    }

    public function addWorkflow(Workflow $workflow): static
    {
        if (!$this->workflows->contains($workflow)) {
            $this->workflows->add($workflow);
            $workflow->setProduct($this);
        }

        return $this;
    }

    public function removeWorkflow(Workflow $workflow): static
    {
        if ($this->workflows->removeElement($workflow)) {
            // set the owning side to null (unless already changed)
            if ($workflow->getProduct() === $this) {
                $workflow->setProduct(null);
            }
        }

        return $this;
    }
}
