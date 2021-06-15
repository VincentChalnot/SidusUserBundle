<?php
declare(strict_types=1);

namespace Sidus\UserBundle\Security\Core\Role;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Used to work with roles and permissions.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class LeafRole
{
    protected ?LeafRole $parent = null;

    /** @var Collection<LeafRole> */
    protected Collection $children;


    public function __construct(private string $role)
    {
        $this->children = new ArrayCollection();
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getParent(): ?LeafRole
    {
        return $this->parent;
    }

    public function setParent(?LeafRole $parent): void
    {
        if ($parent && !$parent->getChildren()->contains($this)) {
            $parent->addChild($this);
        }
        $this->parent = $parent;
    }

    /**
     * @return Collection<LeafRole>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(LeafRole $child): void
    {
        $this->children->add($child);
        $child->setParent($this);
    }

    public function removeChild(LeafRole $child): void
    {
        $this->children->removeElement($child);
        $child->setParent(null);
    }

    /**
     * @param Collection<LeafRole> $children
     */
    public function setChildren(Collection $children): void
    {
        $this->clearChildren();
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }

    public function clearChildren(): void
    {
        foreach ($this->children as $child) {
            $this->removeChild($child);
        }
    }
}
