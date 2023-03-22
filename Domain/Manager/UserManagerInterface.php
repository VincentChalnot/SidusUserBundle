<?php
/*
 * This file is part of the Sidus/UserBundle package.
 *
 * Copyright (c) 2021 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sidus\UserBundle\Domain\Manager;

use Sidus\UserBundle\Model\AdvancedUserInterface;

/**
 * Interface for user manager
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
interface UserManagerInterface
{
    public function findByUsername(string $username): AdvancedUserInterface;

    public function createUser(string $username): AdvancedUserInterface;

    public function setPlainTextPassword(AdvancedUserInterface $user, string $password): void;

    public function requestNewPassword(AdvancedUserInterface $user): void;

    public function save(AdvancedUserInterface $user): void;

    public function remove(AdvancedUserInterface $user): void;

    /*
     * Load a user with its authentication token
     * Only used at first login and when retrieving a lost password
     */
    public function loadUserByToken(string $token): ?AdvancedUserInterface;
}
