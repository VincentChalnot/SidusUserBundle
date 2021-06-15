<?php
declare(strict_types=1);

namespace Sidus\UserBundle\Security\Core\Role;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Role\RoleHierarchy as BaseRoleHierarchy;

/**
 * Used to work with roles.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class RoleHierarchy extends BaseRoleHierarchy
{
    /** @var Collection<LeafRole> */
    protected Collection $treeHierarchy;

    /**
     * @param array $hierarchy An array defining the hierarchy
     */
    public function __construct(array $hierarchy)
    {
        parent::__construct($hierarchy);

        /** @var LeafRole[] $flatRoles */
        $flatRoles = [];
        // Build proper tree hierarchy from security config
        foreach ($hierarchy as $rootRole => $roles) {
            if (!isset($flatRoles[$rootRole])) {
                $flatRoles[$rootRole] = new LeafRole($rootRole);
            }
            /** @var array $roles */
            foreach ($roles as $leafRole) {
                if (!isset($flatRoles[$leafRole])) {
                    $flatRoles[$leafRole] = new LeafRole($leafRole);
                }
                $flatRoles[$rootRole]->addChild($flatRoles[$leafRole]);
            }
        }
        $this->treeHierarchy = new ArrayCollection();
        foreach ($flatRoles as $role) {
            if (!$role->getParent()) {
                $this->treeHierarchy[] = $role;
            }
        }
    }

    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * @return Collection<LeafRole>
     */
    public function getTreeHierarchy(): Collection
    {
        return $this->treeHierarchy;
    }
}
