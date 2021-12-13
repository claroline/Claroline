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
use Claroline\CoreBundle\Entity\Model\GroupsTrait;
use Claroline\CoreBundle\Entity\Model\OrganizationsTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Organization\UserOrganizationReference;
use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\User\UserRepository")
 * @ORM\Table(
 *     name="claro_user",
 *     indexes={
 *         @ORM\Index(name="code_idx", columns={"administrative_code"}),
 *         @ORM\Index(name="enabled_idx", columns={"is_enabled"}),
 *         @ORM\Index(name="is_removed", columns={"is_removed"})
 * })
 * @DoctrineAssert\UniqueEntity("username")
 * @DoctrineAssert\UniqueEntity("email")
 */
class User extends AbstractRoleSubject implements \Serializable, UserInterface, EquatableInterface, IdentifiableInterface
{
    use Id;
    use Uuid;
    use Poster;
    use Thumbnail;
    use Description;
    use GroupsTrait;
    use OrganizationsTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", length=50)
     * @Assert\NotBlank()
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", length=50)
     * @Assert\NotBlank()
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min="3")
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $locale;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $salt;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(min="4", groups={"registration"})
     */
    protected $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(unique=true, name="mail")
     * @Assert\NotBlank()
     * @Assert\Email(strict = true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="administrative_code", nullable=true)
     */
    protected $administrativeCode;

    /**
     * @var Group[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Group",
     *      inversedBy="users"
     * )
     * @ORM\JoinTable(name="claro_user_group")
     */
    protected $groups;

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
     * @var Workspace\Workspace
     *
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(name="workspace_id", onDelete="SET NULL")
     */
    protected $personalWorkspace;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="initialization_date", type="datetime", nullable=true)
     */
    protected $initDate;

    /**
     * @ORM\Column(name="reset_password", nullable=true)
     */
    protected $resetPasswordHash;

    /**
     * @ORM\Column(name="hash_time", type="integer", nullable=true)
     */
    protected $hashTime;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $picture;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $hasAcceptedTerms;

    /**
     *  This should be renamed because this field really means "is not deleted".
     *
     * @var bool
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    protected $isEnabled = true;

    /**
     * @ORM\Column(name="is_removed", type="boolean")
     */
    protected $isRemoved = false;

    /**
     * Avoids any modification on the user. It also excludes it from stats.
     *
     * @ORM\Column(name="is_locked", type="boolean")
     */
    protected $locked = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_mail_notified", type="boolean")
     */
    protected $isMailNotified = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_mail_validated", type="boolean")
     */
    protected $isMailValidated = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiration_date", type="datetime", nullable=true)
     */
    protected $expirationDate;

    /**
     * @ORM\Column(name="email_validation_hash", nullable=true)
     */
    protected $emailValidationHash;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization", inversedBy="administrators")
     * @ORM\JoinTable(name="claro_user_administrator")
     */
    protected $administratedOrganizations;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Location\Location",
     *     inversedBy="users"
     * )
     *
     * @todo relation should not be declared here (only use Unidirectional)
     */
    protected $locations;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\UserOrganizationReference",
     *     mappedBy="user",
     *     cascade={"all"}
     *  )
     * @ORM\JoinColumn(name="user_id", nullable=false)
     */
    protected $userOrganizationReferences;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Task\ScheduledTask",
     *     inversedBy="users"
     * )
     * @ORM\JoinTable(name="claro_scheduled_task_users")
     *
     * @var ArrayCollection
     *
     * @todo relation should not be declared here (only use Unidirectional)
     */
    private $scheduledTasks;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $code;

    public function __construct()
    {
        parent::__construct();

        $this->refreshUuid();
        $this->setEmailValidationHash(uniqid('', true));

        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->scheduledTasks = new ArrayCollection();
        $this->administratedOrganizations = new ArrayCollection();
        $this->userOrganizationReferences = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * Required to store user in session.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'id' => $this->id,
            'username' => $this->username,
            'roles' => $this->getRoles(),
        ]);
    }

    /**
     * Required to store user in session.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $user = unserialize($serialized);

        $this->id = $user['id'];
        $this->username = $user['username'];
        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->administratedOrganizations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    public function getFullName()
    {
        return trim($this->firstName.' '.$this->lastName);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        if (null === $password) {
            return $this;
        }

        $this->password = $password;

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return User
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @param string $plainPassword
     *
     * @return User
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
        $this->password = null;

        return $this;
    }

    /**
     * Returns the user's roles as an array of string values (needed for
     * Symfony security checks). The roles owned by groups the user is a
     * member are included by default.
     *
     * @param bool $areGroupsIncluded
     *
     * @return string[]
     */
    public function getRoles($areGroupsIncluded = true)
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
     * @param bool $areGroupsIncluded
     *
     * @return Role[]
     */
    public function getEntityRoles($areGroupsIncluded = true)
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
    public function getGroupRoles()
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
     * @param bool   $includeGroup
     * @param string $roleName
     *
     * @return bool
     */
    public function hasRole($roleName, $includeGroup = true)
    {
        $roles = $this->getEntityRoles($includeGroup);
        $roleNames = array_map(function (Role $role) {
            return $role->getName();
        }, $roles);

        return in_array($roleName, $roleNames);
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;

        return $this;
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

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdministrativeCode()
    {
        return $this->administrativeCode;
    }

    /**
     * @param string $administrativeCode
     *
     * @return User
     */
    public function setAdministrativeCode($administrativeCode)
    {
        $this->administrativeCode = $administrativeCode;

        return $this;
    }

    /**
     * @param Workspace\Workspace $workspace
     *
     * @return User
     */
    public function setPersonalWorkspace($workspace)
    {
        $this->personalWorkspace = $workspace;

        return $this;
    }

    /**
     * @return Workspace\Workspace
     */
    public function getPersonalWorkspace()
    {
        return $this->personalWorkspace;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->created;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets the user creation date.
     *
     * NOTE : creation date is already handled by the timestamp listener; this
     *        setter exists mainly for testing purposes.
     */
    public function setCreationDate(\DateTime $date)
    {
        $this->created = $date;
    }

    public function getResetPasswordHash()
    {
        return $this->resetPasswordHash;
    }

    public function setResetPasswordHash($resetPasswordHash)
    {
        $this->resetPasswordHash = $resetPasswordHash;
    }

    public function getHashTime()
    {
        return $this->hashTime;
    }

    public function setHashTime($hashTime)
    {
        $this->hashTime = $hashTime;
    }

    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function hasAcceptedTerms()
    {
        return $this->hasAcceptedTerms;
    }

    public function setAcceptedTerms($boolean)
    {
        $this->hasAcceptedTerms = $boolean;
    }

    public function isAccountNonExpired()
    {
        foreach ($this->getRoles() as $role) {
            if ('ROLE_ADMIN' === $role) {
                return true;
            }
        }

        return $this->getExpirationDate() >= new \DateTime();
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function setLocked(bool $locked)
    {
        $this->locked = $locked;
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }

    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    public function setIsMailNotified($isMailNotified)
    {
        $this->isMailNotified = $isMailNotified;
    }

    public function isMailNotified()
    {
        return $this->isMailNotified;
    }

    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    public function getExpirationDate()
    {
        $defaultExpirationDate = (strtotime('2100-01-01')) ? '2100-01-01' : '2038-01-01';

        return (null !== $this->expirationDate && $this->expirationDate->getTimestamp()) ?
            $this->expirationDate :
            new \DateTime($defaultExpirationDate);
    }

    public function setInitDate($initDate)
    {
        $this->initDate = $initDate;
    }

    public function getInitDate()
    {
        return $this->initDate;
    }

    public function setIsMailValidated($isMailValidated)
    {
        $this->isMailValidated = $isMailValidated;
    }

    public function isMailValidated()
    {
        return $this->isMailValidated;
    }

    public function setEmailValidationHash($hash)
    {
        $this->emailValidationHash = $hash;
    }

    public function getEmailValidationHash()
    {
        return $this->emailValidationHash;
    }

    /**
     * @param bool $includeGroups
     *
     * @return array
     *
     * @todo this should return an array collection
     */
    public function getOrganizations($includeGroups = true)
    {
        $organizations = [];

        if ($includeGroups) {
            foreach ($this->groups as $group) {
                array_merge($organizations, $group->getOrganizations()->toArray());
            }
        }

        $userOrganizations = $this->userOrganizationReferences->toArray();
        $userOrganizations = array_map(function (UserOrganizationReference $ref) {
            return $ref->getOrganization();
        }, $userOrganizations);

        return array_merge($organizations, $userOrganizations);
    }

    public function addOrganization(Organization $organization)
    {
        if ($organization->getMaxUsers() > -1) {
            $totalUsers = count($organization->getUserOrganizationReferecnes());
            if ($totalUsers >= $organization->getMaxUsers()) {
                throw new \Exception('The organization user limit has been reached');
            }
        }

        $found = false;
        foreach ($this->userOrganizationReferences as $userOrgaRef) {
            if ($userOrgaRef->getOrganization() === $organization && $userOrgaRef->getUser() === $this) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $ref = new UserOrganizationReference();
            $ref->setOrganization($organization);
            $ref->setUser($this);

            $this->userOrganizationReferences->add($ref);
        }
    }

    public function removeOrganization(Organization $organization)
    {
        $found = null;

        foreach ($this->userOrganizationReferences as $ref) {
            if ($ref->getOrganization()->getId() === $organization->getId()) {
                $found = $ref;
            }
        }

        if ($found) {
            $this->userOrganizationReferences->removeElement($found);
            //this is the line doing all the work. I'm not sure the previous one is usefull
            $found->getOrganization()->removeUser($this);
        }
    }

    public function getAdministratedOrganizations()
    {
        return $this->administratedOrganizations;
    }

    public function addAdministratedOrganization(Organization $organization)
    {
        $this->addOrganization($organization);

        if (!$this->administratedOrganizations->contains($organization)) {
            $this->administratedOrganizations->add($organization);
        }
    }

    public function removeAdministratedOrganization(Organization $organization)
    {
        $this->administratedOrganizations->removeElement($organization);
    }

    public function setAdministratedOrganizations($organizations)
    {
        $this->administratedOrganizations = $organizations;
    }

    public function getMainOrganization()
    {
        foreach ($this->userOrganizationReferences as $ref) {
            if ($ref->isMain()) {
                return $ref->getOrganization();
            }
        }

        return null;
    }

    public function setMainOrganization(Organization $organization)
    {
        $this->addOrganization($organization);

        foreach ($this->userOrganizationReferences as $ref) {
            if ($ref->isMain()) {
                $ref->setIsMain(false);
            }

            if ($ref->getOrganization()->getUuid() === $organization->getUuid()) {
                $ref->setIsMain(true);
            }
        }
    }

    public function setRemoved($isRemoved)
    {
        $this->isRemoved = $isRemoved;
    }

    //alias
    public function remove()
    {
        $this->setRemoved(true);
    }

    public function isRemoved()
    {
        return $this->isRemoved;
    }

    public function enable()
    {
        $this->isEnabled = true;
    }

    public function disable()
    {
        $this->isEnabled = false;
    }

    public function clearRoles()
    {
        foreach ($this->roles as $role) {
            if ('ROLE_USER' !== $role->getName()) {
                $this->removeRole($role);
            }
        }
    }

    public function setLastLogin(\DateTime $date)
    {
        $this->lastLogin = $date;
    }

    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    public function getLocations()
    {
        return $this->locations;
    }

    public function addScheduledTask(ScheduledTask $task)
    {
        $this->scheduledTasks->add($task);
    }

    public function removeScheduledTask(ScheduledTask $task)
    {
        $this->scheduledTasks->removeElement($task);
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }
}
