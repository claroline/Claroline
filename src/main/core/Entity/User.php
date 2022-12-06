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

use Claroline\AppBundle\Entity\IdentifiableInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\CommunityBundle\Model\HasGroups;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Organization\UserOrganizationReference;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CommunityBundle\Repository\UserRepository")
 * @ORM\Table(
 *     name="claro_user",
 *     indexes={
 *         @ORM\Index(name="code_idx", columns={"administrative_code"}),
 *         @ORM\Index(name="enabled_idx", columns={"is_enabled"}),
 *         @ORM\Index(name="is_removed", columns={"is_removed"})
 * })
 */
class User extends AbstractRoleSubject implements \Serializable, UserInterface, EquatableInterface, IdentifiableInterface
{
    use Id;
    use Uuid;
    use Poster;
    use Thumbnail;
    use Description;
    use HasGroups;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", length=50)
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", length=50)
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min="3")
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    private $salt;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(min="4", groups={"registration"})
     */
    private $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(unique=true, name="mail")
     * @Assert\NotBlank()
     * @Assert\Email(strict = true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="administrative_code", nullable=true)
     */
    private $administrativeCode;

    /**
     * @var Group[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Group",
     *      inversedBy="users"
     * )
     * @ORM\JoinTable(name="claro_user_group")
     */
    private $groups;

    /**
     * @var Role[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="users",
     *     fetch="EXTRA_LAZY",
     *     cascade={"merge", "refresh"}
     * )
     * @ORM\JoinTable(name="claro_user_role")
     */
    protected $roles;

    /**
     * @var Workspace
     *
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(name="workspace_id", onDelete="SET NULL")
     */
    private $personalWorkspace;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_activity", type="datetime", nullable=true)
     */
    private $lastActivity;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="initialization_date", type="datetime", nullable=true)
     */
    private $initDate;

    /**
     * @ORM\Column(name="reset_password", nullable=true)
     */
    private $resetPasswordHash;

    /**
     * @ORM\Column(nullable=true)
     */
    private $picture;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $hasAcceptedTerms = false;

    /**
     *  This should be renamed because this field really means "is not deleted".
     *
     * @var bool
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled = true;

    /**
     * @ORM\Column(name="is_removed", type="boolean")
     */
    private $isRemoved = false;

    /**
     * Avoids any modification on the user.
     *
     * @ORM\Column(name="is_locked", type="boolean")
     *
     * @deprecated
     */
    private $locked = false;

    /**
     * A technical user is only creatable from the command line/code, cannot be modified,
     * and is hidden in the searches.
     * This is useful to create a support user in the platform.
     *
     * @ORM\Column(type="boolean", options={"default"= 0})
     *
     * @var bool
     */
    private $technical = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_mail_notified", type="boolean")
     */
    private $isMailNotified = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_mail_validated", type="boolean")
     */
    private $isMailValidated = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiration_date", type="datetime", nullable=true)
     */
    private $expirationDate;

    /**
     * @ORM\Column(name="email_validation_hash", nullable=true)
     */
    private $emailValidationHash;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Location\Location",
     *     inversedBy="users"
     * )
     *
     * @deprecated relation should not be declared here (only use Unidirectional)
     */
    private $locations;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\UserOrganizationReference",
     *     mappedBy="user",
     *     cascade={"all"}
     *  )
     * @ORM\JoinColumn(name="user_id", nullable=false)
     *
     * @var ArrayCollection|UserOrganizationReference[]
     */
    private $userOrganizationReferences;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $code;

    public function __construct()
    {
        parent::__construct();

        $this->refreshUuid();
        $this->setEmailValidationHash(uniqid('', true));

        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->userOrganizationReferences = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * Required to store user in session.
     */
    public function serialize(): string
    {
        return serialize([
            'id' => $this->id,
            'username' => $this->username,
            'roles' => $this->getRoles(),
        ]);
    }

    /**
     * Required to store user in session.
     */
    public function unserialize($serialized)
    {
        $user = unserialize($serialized);

        $this->id = $user['id'];
        $this->username = $user['username'];
        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
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

    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
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
        $roles = [];
        if ($this->roles) {
            $roles = $this->roles->toArray();
        }

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

    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Checks if the user has a given role.
     *
     * @param string|Role $role
     */
    public function hasRole($role, ?bool $includeGroup = true): bool
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

        if (!$user->isEnabled() || $user->isRemoved()) {
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
        foreach ($this->getRoles() as $role) {
            if ('ROLE_ADMIN' === $role) {
                return true;
            }
        }

        return empty($this->getExpirationDate()) || $this->getExpirationDate() >= new \DateTime();
    }

    /**
     * @deprecated
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @deprecated
     */
    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function isTechnical(): bool
    {
        return $this->technical;
    }

    public function setTechnical(bool $technical): void
    {
        $this->technical = $technical;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function setIsMailNotified(bool $isMailNotified): void
    {
        $this->isMailNotified = $isMailNotified;
    }

    public function isMailNotified(): bool
    {
        return $this->isMailNotified;
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

    public function setIsMailValidated($isMailValidated): void
    {
        $this->isMailValidated = $isMailValidated;
    }

    public function isMailValidated(): bool
    {
        return $this->isMailValidated;
    }

    public function setEmailValidationHash($hash): void
    {
        $this->emailValidationHash = $hash;
    }

    public function getEmailValidationHash(): ?string
    {
        return $this->emailValidationHash;
    }

    public function hasOrganization(Organization $organization, ?bool $includeGroup = true): bool
    {
        $organizations = $this->getOrganizations($includeGroup);
        foreach ($organizations as $userOrganization) {
            if ($userOrganization->getId() === $organization->getId()) {
                return true;
            }
        }

        return false;
    }

    public function getOrganizations(?bool $includeGroups = true): array
    {
        $organizations = [];

        if ($includeGroups) {
            foreach ($this->groups as $group) {
                foreach ($group->getOrganizations() as $groupOrganization) {
                    $organizations[$groupOrganization->getId()] = $groupOrganization;
                }
            }
        }

        foreach ($this->userOrganizationReferences as $userOrganizationReference) {
            $organizations[$userOrganizationReference->getOrganization()->getId()] = $userOrganizationReference->getOrganization();
        }

        return array_values($organizations);
    }

    public function addOrganization(Organization $organization, ?bool $managed = false): void
    {
        if ($organization->getMaxUsers() > -1) {
            $totalUsers = count($organization->getUserOrganizationReferences());
            if ($totalUsers >= $organization->getMaxUsers()) {
                throw new \Exception('The organization user limit has been reached');
            }
        }

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

            $this->userOrganizationReferences->add($ref);
        }

        $ref->setManager($managed);
    }

    public function removeOrganization(Organization $organization): void
    {
        /** @var UserOrganizationReference $found */
        $found = null;

        foreach ($this->userOrganizationReferences as $ref) {
            if ($ref->getOrganization()->getId() === $organization->getId()) {
                $found = $ref;
            }
        }

        if ($found) {
            $this->userOrganizationReferences->removeElement($found);
            //this is the line doing all the work. I'm not sure the previous one is useful
            $found->getOrganization()->removeUser($this);
        }
    }

    public function getAdministratedOrganizations(): ArrayCollection
    {
        $managedOrganizations = new ArrayCollection();
        foreach ($this->userOrganizationReferences as $userRef) {
            if ($userRef->isManager()) {
                $managedOrganizations->add($userRef->getOrganization());
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

    //alias
    public function remove(): void
    {
        $this->setRemoved(true);
    }

    public function isRemoved(): bool
    {
        return $this->isRemoved;
    }

    public function enable(): void
    {
        $this->isEnabled = true;
    }

    public function disable(): void
    {
        $this->isEnabled = false;
    }

    public function clearRoles(): void
    {
        foreach ($this->roles as $role) {
            if ('ROLE_USER' !== $role->getName()) {
                $this->removeRole($role);
            }
        }
    }

    public function setLastActivity(\DateTimeInterface $date): void
    {
        $this->lastActivity = $date;
    }

    public function getLastActivity(): ?\DateTimeInterface
    {
        return $this->lastActivity;
    }

    public function getLocations()
    {
        return $this->locations;
    }

    public function setCode($code): void
    {
        $this->code = $code;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }
}
