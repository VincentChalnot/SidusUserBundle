<?php
/*
 * This file is part of the Sidus/MediaBundle package.
 *
 * Copyright (c) 2021 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sidus\UserBundle\Entity;

/**
 * Simplify role handling
 */
trait RoleCollectionTrait
{
    public function getRoles(): array
    {
        $roles = $this->roles;

        // we need to make sure to have at least the user role
        $roles[] = static::ROLE_USER;

        return array_unique($roles);
    }

    public function hasRole(string $role): bool
    {
        return \in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);
        if (static::ROLE_USER === $role) {
            return;
        }

        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }
    }

    public function removeRole(string $role): void
    {
        $key = array_search(strtoupper($role), $this->roles, true);
        if (false !== $key) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function setSuperAdmin(bool $status): void
    {
        if (true === $status) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }
    }

    public function getRawRoles(): array
    {
        return $this->roles;
    }

    public function setRawRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPrintableRoles(): array
    {
        $roles = [];
        foreach ($this->getRoles() as $role) {
            if (static::ROLE_USER === $role) {
                continue;
            }
            $role = strtolower(str_replace('ROLE_', '', $role));
            $label = ucwords(trim(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $role)));
            $roles[$role] = $label;
        }

        return $roles;
    }
}
