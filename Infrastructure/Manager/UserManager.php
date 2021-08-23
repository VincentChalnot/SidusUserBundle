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

namespace Sidus\UserBundle\Infrastructure\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Sidus\UserBundle\Domain\Manager\UserManagerInterface;
use Sidus\UserBundle\Entity\User;
use Sidus\UserBundle\Exception\BadUsernameException;
use Sidus\UserBundle\Mailer\UserMailer;
use Sidus\UserBundle\Model\AdvancedUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Handles the creation, deletion and update of users
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class UserManager implements UserManagerInterface
{
    protected EntityManagerInterface $entityManager;

    public function __construct(
        protected UserProviderInterface $userProvider,
        protected UserPasswordHasherInterface $passwordHasher,
        protected ValidatorInterface $validator,
        protected UserMailer $userMailer,
        protected LoggerInterface $logger,
        ManagerRegistry $doctrine
    ) {
        $entityManager = $doctrine->getManagerForClass(User::class);
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \UnexpectedValueException('No manager found for class '.User::class);
        }
        $this->entityManager = $entityManager;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->userProvider->loadUserByIdentifier($identifier);
    }

    public function createUser(string $email): AdvancedUserInterface
    {
        $user = new User();
        $user->setEmail($email);
        $violations = $this->validator->validate($user);
        if ($violations->count() > 0) {
            throw BadUsernameException::createFromViolations($violations);
        }

        return $user;
    }

    public function setPlainTextPassword(AdvancedUserInterface $user, string $password): void
    {
        $encoded = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($encoded);
        $user->setPasswordRequestedAt(null);
        $user->unsetAuthenticationToken();
        $user->setNew(false);
    }

    public function requestNewPassword(AdvancedUserInterface $user): void
    {
        $user->setPasswordRequestedAt(new \DateTimeImmutable());
        $user->resetAuthenticationToken();
        $user->setEmailSentAt(null);
        $this->save($user);
    }

    public function save(AdvancedUserInterface $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if ($user->isNew() && !$user->getEmailSentAt()) {
            $this->userMailer->sendNewUserMail($user);
            $user->setEmailSentAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }
        if ($user->getPasswordRequestedAt() && !$user->getEmailSentAt()) {
            $this->userMailer->sendResetPasswordMail($user);
            $user->setEmailSentAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }
    }

    public function remove(AdvancedUserInterface $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function loadUserByUsername(string $username): AdvancedUserInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->userProvider->loadUserByIdentifier($username);
    }

    public function loadUserByToken(string $token): ?AdvancedUserInterface
    {
        return $this->getRepository()->findOneBy(
            [
                'authenticationToken' => $token,
            ]
        );
    }

    public function refreshUser(UserInterface $user): AdvancedUserInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->userProvider->refreshUser($user);
    }

    public function supportsClass(string $class): bool
    {
        return $this->userProvider->supportsClass($class);
    }

    protected function getRepository(): EntityRepository
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->entityManager->getRepository(User::class);
    }
}
