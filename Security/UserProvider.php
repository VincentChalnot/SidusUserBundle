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

namespace Sidus\UserBundle\Security;

use Doctrine\Persistence\ManagerRegistry;
use Sidus\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Load the user for the firewall
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class UserProvider implements UserProviderInterface
{
    protected EntityManagerInterface $entityManager;
    protected string $userClass;

    public function __construct(ManagerRegistry $doctrine, string $userClass = User::class)
    {
        $entityManager = $doctrine->getManagerForClass($userClass);
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \UnexpectedValueException("No manager found for class {$userClass}");
        }
        $this->entityManager = $entityManager;
        $this->userClass = $userClass;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByUsername($identifier);
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        $user = $this->getRepository()->findOneBy(['email' => $username]);
        if ($user instanceof UserInterface) {
            return $user;
        }

        throw new UserNotFoundException($username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass(string $class): bool
    {
        return is_a($class, $this->userClass, true);
    }

    protected function getRepository(): EntityRepository
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->entityManager->getRepository($this->userClass);
    }
}
