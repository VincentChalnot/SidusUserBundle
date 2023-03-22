<?php

declare(strict_types=1);

namespace Sidus\UserBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sidus\UserBundle\Model\AdvancedUserInterface;
use Sidus\UserBundle\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'sidus_user')]
#[ORM\Index(columns: ['email'], name: 'sidus_user_email_idx')]
#[ORM\Index(columns: ['created_at'], name: 'sidus_user_created_at_idx')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class User implements AdvancedUserInterface
{
    use RoleCollectionTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'guid', length: 16, unique: true)]
    protected string $identifier;

    #[ORM\Column(type: 'datetime_immutable')]
    protected DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'string', unique: true)]
    protected ?string $email = null;

    #[ORM\Column(type: 'json')]
    protected array $roles = [];

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $password = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected ?DateTimeImmutable $passwordRequestedAt = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $authenticationToken = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $new = true;

    #[ORM\Column(type: 'boolean')]
    protected bool $enabled = true;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected ?DateTimeImmutable $emailSentAt = null;

    /**
     * @var Collection<Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'sidus_user_group')]
    #[ORM\JoinColumn(name: 'user_id')]
    #[ORM\InverseJoinColumn(name: 'group_id')]
    protected Collection $groups;

    public function __construct()
    {
        $this->identifier = (new Ulid())->toRfc4122();
        $this->createdAt = new DateTimeImmutable();
        $this->groups = new ArrayCollection();
        $this->resetAuthenticationToken();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername()
            ?? throw new \LogicException('User::getUserIdentifier is unsupported before user initialization');
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // we need to make sure to have at least the user role
        $roles[] = static::ROLE_USER;
        foreach ($this->getGroups() as $group) {
            foreach ($group->getRoles() as $role) {
                $roles[] = $role;
            }
        }

        return array_unique($roles);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): void
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getAuthenticationToken(): ?string
    {
        return $this->authenticationToken;
    }

    public function resetAuthenticationToken(): void
    {
        $this->authenticationToken = bin2hex(random_bytes(64));
    }

    public function unsetAuthenticationToken(): void
    {
        $this->authenticationToken = null;
    }

    public function isNew(): bool
    {
        return $this->new;
    }

    public function setNew(bool $new): void
    {
        $this->new = $new;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getEmailSentAt(): ?DateTimeImmutable
    {
        return $this->emailSentAt;
    }

    public function setEmailSentAt(?DateTimeImmutable $emailSentAt): void
    {
        $this->emailSentAt = $emailSentAt;
    }

    /**
     * @return Collection<Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(Group $group): void
    {
        $this->groups->add($group);
    }

    public function removeGroup(Group $group): void
    {
        $this->groups->removeElement($group);
    }

    public function clearGroups(): void
    {
        $this->groups->clear();
    }

    public function getPasswordRequestedAt(): ?DateTimeImmutable
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?DateTimeImmutable $passwordRequestedAt): void
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    }

    public function serialize(): string
    {
        return serialize(
            [
                $this->identifier,
                $this->email,
                $this->password,
                $this->roles,
            ]
        );
    }

    public function unserialize(string $serialized): void
    {
        [
            $this->identifier,
            $this->email,
            $this->password,
            $this->roles,
        ] = unserialize($serialized, ['allowed_classes' => $this::class]);
    }

    public function __toString(): string
    {
        return (string) $this->getUsername();
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $this->getUserIdentifier() === $user->getUserIdentifier();
    }
}
