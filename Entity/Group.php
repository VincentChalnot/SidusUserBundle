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

namespace Sidus\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'sidus_group')]
#[ORM\Index(columns: ['name'], name: 'sidus_group_name_idx')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[UniqueEntity(fields: ['identifier'])]
#[UniqueEntity(fields: ['name'])]
class Group
{
    use RoleCollectionTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'guid', length: 16, unique: true)]
    protected string $identifier;

    #[ORM\Column(type: 'string', unique: true)]
    protected ?string $name = null;

    #[ORM\Column(type: 'datetime_immutable')]
    protected \DateTimeInterface $createdAt;

    /**
     * @var Collection<User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'groups')]
    protected Collection $users;

    #[ORM\Column(type: 'json')]
    protected array $roles = [];

    public function __construct()
    {
        $this->identifier = (new Ulid())->toRfc4122();
        $this->createdAt = new \DateTimeImmutable();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUsers(): ArrayCollection|Collection
    {
        return $this->users;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }
}
