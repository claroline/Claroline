<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Disabled;
use Claroline\CommunityBundle\Model\HasGroups;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Organization\UserOrganizationReference;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CommunityBundle\Repository\UserRepository")
 *
 * @ORM\Table(
 *     name="claro_user",
 *     indexes={
 *
 *         @ORM\Index(name="disabled_idx", columns={"is_disabled"}),
 *         @ORM\Index(name="is_removed", columns={"is_removed"})
 * })
 */
class User extends AbstractRoleSubject implements UserInterface, EquatableInterface, PasswordAuthenticatedUserInterface, LegacyPasswordAuthenticatedUserInterface, CrudEntityInterface, ContextSubjectInterface
{
    use Id;
    use Uuid;
    use Poster;
    use Thumbnail;
    use Description;
    use Disabled;
    use HasGroups;

    /**
     * @ORM\Column(name="first_name", length=50)
     *
     * @Assert\NotBlank()
     */
    private ?string $firstName = null;

    /**
     * @ORM\Column(name="last_name", length=50)
     *
     * @Assert\NotBlank()
     */
    private ?string $lastName = null;

    /**
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank()
     */
    private ?string $username = null;

    /**
     * @ORM\Column()
     */
    private ?string $password = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $locale = null;

    /**
     * @ORM\Column()
     */
    private string $salt;

    private ?string $plainPassword = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $phone = null;

    /**
     * @ORM\Column(unique=true, name="mail")
     *
     * @Assert\NotBlank()
     *
     * @Assert\Email(mode="strict")
     */
    private ?string $email = null;

    /**
     * @ORM\Column(name="administrative_code", nullable=true)
     */
    private ?string $administrativeCode = null;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Group")
     *
     * @ORM\JoinTable(name="claro_user_group")
     */
    private Collection $groups;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="users",
     *     fetch="EXTRA_LAZY"
     * )
     *
     * @ORM\JoinTable(name="claro_user_role")
     */
    protected Collection $roles;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     *
     * @ORM\JoinColumn(name="workspace_id", onDelete="SET NULL")
     */
    private ?Workspace $personalWorkspace = null;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private ?\DateTimeInterface $created = null;

    /**
     * @ORM\Column(name="last_activity", type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $lastActivity = null;

    /**
     * @ORM\Column(name="initialization_date", type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $initDate = null;

    /**
     * @ORM\Column(name="reset_password", nullable=true)
     */
    private ?string $resetPasswordHash = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $picture = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $hasAcceptedTerms = false;

    /**
     * @ORM\Column(name="is_removed", type="boolean")
     */
    private bool $isRemoved = false;

    /**
     * A technical user is only creatable from the command line/code, cannot be modified,
     * and is hidden in the searches.
     * This is useful to create a support user in the platform.
     *
     * @ORM\Column(type="boolean", options={"default"= 0})
     */
    private bool $technical = false;

    /**
     * @ORM\Column(name="is_mail_notified", type="boolean")
     */
    private bool $mailNotified = true;

    /**
     * @ORM\Column(name="is_mail_validated", type="boolean")
     */
    private bool $mailValidated = false;

    /**
     * @ORM\Column(name="expiration_date", type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $expirationDate = null;

    /**
     * @ORM\Column(name="email_validation_hash", nullable=true)
     */
    private ?string $emailValidationHash = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\UserOrganizationReference",
     *     mappedBy="user",
     *     orphanRemoval=true,
     *     cascade={"persist"},
     *     fetch="EXTRA_LAZY"
     *  )
     *
     * @ORM\JoinColumn(name="user_id", nullable=false)
     */
    private Collection $userOrganizationReferences;

    /**
     * @ORM\Column(name="user_status", nullable=true)
     */
    private ?string $status = null;

    public function __construct()
    {
        parent::__construct();

        $this->refreshUuid();
        $this->setEmailValidationHash(uniqid('', true));

        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->userOrganizationReferences = new ArrayCollection();
    }

    public static function getIdentifiers(): array
    {
        return ['username', 'email'];
    }

    public function __toString()
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * Required to store user in session.
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
        ];
    }

    /**
     * Required to store user in session.
     */
    public function __unserialize(array $data)
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getContextIdentifier(): string
    {
        return $this->uuid;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getFullName(): ?string
    {
        return trim($this->firstName.' '.$this->lastName);
    }

    public function getStatus(): ?string
    {
        if (!empty($this->status)) {
            // return user defined status
            return $this->status;
        }

        if ($this->lastActivity) {
            $now = new \DateTime();
            $now = $now->sub(\DateInterval::createFromDateString('15 minute'));

            if ($this->lastActivity >= $now) {
                return 'online';
            }

            $now = $now->sub(\DateInterval::createFromDateString('30 minute'));
            if ($this->lastActivity >= $now) {
                return 'absent';
            }
        }

        return 'offline';
    }

    public function setStatus(string $status = null): void
    {
        $this->status = $status;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setPassword(?string $password): void
    {
        if (null !== $password) {
            $this->password = $password;
        }
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
        $this->password = null;
    }

    /**
     * Returns the user's roles as an array of string values (needed for
     * Symfony security checks). The roles owned by groups the user is a
     * member are included by default.
     *
     * @return string[]
     */
    public function getRoles(?bool $areGroupsIncluded = true): array
    {
        $roleNames = parent::getRoles();

        if ($areGroupsIncluded) {
            foreach ($this->groups as $group) {
                $roleNames = array_unique(array_merge($roleNames, $group->getRoles()));
            }
        }

        return $roleNames;
    }

    /**
     * Returns the user's roles as an array of entities. The roles
     * owned by groups the user is a member are included by default.
     *
     * @return Role[]
     */
    public function getEntityRoles(?bool $areGroupsIncluded = true): array
    {
        $roles = $this->roles->toArray();

        if ($areGroupsIncluded) {
            foreach ($this->groups as $group) {
                foreach ($group->getEntityRoles() as $role) {
                    if (!in_array($role, $roles)) {
                        $roles[] = $role;
                    }
                }
            }
        }

        return $roles;
    }

    /**
     * Returns the roles owned by groups the user is a member.
     *
     * @return Role[]
     */
    public function getGroupRoles(): array
    {
        $roles = [];

        foreach ($this->groups as $group) {
            foreach ($group->getEntityRoles() as $role) {
                if (!in_array($role, $roles)) {
                    $roles[] = $role;
                }
            }
        }

        return $roles;
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * Checks if the user has a given role.
     */
    public function hasRole(Role|string $role, ?bool $includeGroup = true): bool
    {
        $roleName = $role instanceof Role ? $role->getName() : $role;

        $roles = $this->getEntityRoles($includeGroup);
        $roleNames = array_map(function (Role $role) {
            return $role->getName();
        }, $roles);

        return in_array($roleName, $roleNames);
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (0 === count($user->getRoles())) {
            return false;
        }

        if ($user->isDisabled() || $user->isRemoved()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        if ($this->id !== $user->getId()) {
            return false;
        }

        return true;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getAdministrativeCode(): ?string
    {
        return $this->administrativeCode;
    }

    public function setAdministrativeCode(?string $administrativeCode): void
    {
        $this->administrativeCode = $administrativeCode;
    }

    public function setPersonalWorkspace(Workspace $workspace): void
    {
        $this->personalWorkspace = $workspace;
    }

    public function getPersonalWorkspace(): ?Workspace
    {
        return $this->personalWorkspace;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    /**
     * Sets the user creation date.
     *
     * NOTE : creation date is already handled by the timestamp listener; this
     *        setter exists mainly for testing purposes.
     */
    public function setCreationDate(\DateTime $date): void
    {
        $this->created = $date;
    }

    public function getResetPasswordHash(): ?string
    {
        return $this->resetPasswordHash;
    }

    public function setResetPasswordHash(?string $resetPasswordHash): void
    {
        $this->resetPasswordHash = $resetPasswordHash;
    }

    public function setPicture(?string $picture): void
    {
        $this->picture = $picture;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function hasAcceptedTerms(): bool
    {
        return $this->hasAcceptedTerms;
    }

    public function setAcceptedTerms(bool $hasAcceptedTerms): void
    {
        $this->hasAcceptedTerms = $hasAcceptedTerms;
    }

    public function isAccountNonExpired(): bool
    {
        $roles = $this->getRoles();
        if (in_array(PlatformRoles::ADMIN, $roles, true)) {
            return true;
        }

        return empty($this->getExpirationDate()) || $this->getExpirationDate() >= new \DateTime();
    }

    public function isTechnical(): bool
    {
        return $this->technical;
    }

    public function setTechnical(bool $technical): void
    {
        $this->technical = $technical;
    }

    public function setMailNotified(bool $mailNotified): void
    {
        $this->mailNotified = $mailNotified;
    }

    public function isMailNotified(): bool
    {
        return $this->mailNotified;
    }

    public function setExpirationDate(?\DateTimeInterface $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setInitDate(?\DateTimeInterface $initDate): void
    {
        $this->initDate = $initDate;
    }

    public function getInitDate(): ?\DateTimeInterface
    {
        return $this->initDate;
    }

    public function setMailValidated(bool $mailValidated): void
    {
        $this->mailValidated = $mailValidated;
    }

    public function isMailValidated(): bool
    {
        return $this->mailValidated;
    }

    public function setEmailValidationHash($hash): void
    {
        $this->emailValidationHash = $hash;
    }

    public function getEmailValidationHash(): ?string
    {
        return $this->emailValidationHash;
    }

    public function hasOrganization(Organization $organization, bool $managed = false): bool
    {
        $organizations = $managed ? $this->getAdministratedOrganizations() : $this->getOrganizations();
        foreach ($organizations as $userOrganization) {
            if ($userOrganization->getId() === $organization->getId()) {
                return true;
            }
        }

        return false;
    }

    public function getOrganizations(): array
    {
        $organizations = [];

        foreach ($this->userOrganizationReferences as $userOrganizationReference) {
            $organizations[] = $userOrganizationReference->getOrganization();
        }

        return $organizations;
    }

    public function addOrganization(Organization $organization, ?bool $managed = false): void
    {
        $ref = null;
        foreach ($this->userOrganizationReferences as $userRef) {
            if ($userRef->getOrganization() === $organization && $userRef->getUser() === $this) {
                $ref = $userRef;
                break;
            }
        }

        if (empty($ref)) {
            $ref = new UserOrganizationReference();
            $ref->setOrganization($organization);
            $ref->setUser($this);
            $ref->setManager($managed);

            $this->userOrganizationReferences->add($ref);
        }
    }

    public function removeOrganization(Organization $organization): void
    {
        /** @var UserOrganizationReference $found */
        $found = null;

        foreach ($this->userOrganizationReferences as $ref) {
            if ($ref->getOrganization()->getId() === $organization->getId()) {
                $found = $ref;
                break;
            }
        }

        if ($found) {
            $this->userOrganizationReferences->removeElement($found);
        }
    }

    public function getAdministratedOrganizations(): array
    {
        $managedOrganizations = [];
        foreach ($this->userOrganizationReferences as $userRef) {
            if ($userRef->isManager()) {
                $managedOrganizations[] = $userRef->getOrganization();
            }
        }

        return $managedOrganizations;
    }

    public function addAdministratedOrganization(Organization $organization): void
    {
        $this->addOrganization($organization, true);
    }

    public function removeAdministratedOrganization(Organization $organization): void
    {
        $this->removeOrganization($organization);
    }

    public function getMainOrganization(): ?Organization
    {
        foreach ($this->userOrganizationReferences as $ref) {
            if ($ref->isMain()) {
                return $ref->getOrganization();
            }
        }

        return null;
    }

    public function setMainOrganization(Organization $organization): void
    {
        $this->addOrganization($organization);

        foreach ($this->userOrganizationReferences as $ref) {
            if ($ref->isMain()) {
                $ref->setMain(false);
            }

            if ($ref->getOrganization()->getUuid() === $organization->getUuid()) {
                $ref->setMain(true);
            }
        }
    }

    public function setRemoved($isRemoved): void
    {
        $this->isRemoved = $isRemoved;
    }

    public function isRemoved(): bool
    {
        return $this->isRemoved;
    }

    public function setLastActivity(\DateTimeInterface $date): void
    {
        $this->lastActivity = $date;
    }

    public function getLastActivity(): ?\DateTimeInterface
    {
        return $this->lastActivity;
    }
}
