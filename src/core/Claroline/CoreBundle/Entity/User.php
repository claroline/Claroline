<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\BadgeBundle\Entity\Badge;
use \Serializable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Role;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\UserRepository")
 * @ORM\Table(name="claro_user")
 * @DoctrineAssert\UniqueEntity("username")
 *
 * @todo implement AdvancedUserInterface
 */
class User extends AbstractRoleSubject implements Serializable, UserInterface, EquatableInterface
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @Assert\Regex(
     *     pattern="/^[\w]*$/",
     *     message="special_char_not_allowed"
     * )
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
     * @ORM\Column()
     */
    protected $salt;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="4")
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
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Assert\Email(checkMX = false)
     */
    protected $mail;

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
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\JoinTable(name="claro_user_role")
     */
    protected $roles;

    /**
     * @var AbstractResource[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     mappedBy="creator"
     * )
     */
    protected $resourceNodes;

    /**
     * @var Workspace\AbstractWorkspace
     *
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace",
     *     inversedBy="personalUser",
     *     cascade={"remove"}
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
     * @var UserMessage[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\UserMessage",
     *     mappedBy="user"
     * )
     */
    protected $userMessages;

    /**
     * @var DesktopTool[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     mappedBy="user"
     * )
     */
    protected $orderedTools;

    /**
     * @ORM\Column(name="reset_password", nullable=true)
     */
    protected $resetPasswordHash;

    /**
     * @ORM\Column(name="hash_time", type="integer", nullable=true)
     */
    protected $hashTime;

    /**
     * @var UserBadge[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Claroline\BadgeBundle\Entity\UserBadge", mappedBy="user", cascade={"all"})
     */
    protected $userBadges;

    /**
     * @var UserBadge[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Claroline\BadgeBundle\Entity\UserBadge", mappedBy="issuer", cascade={"all"})
     */
    protected $issuedBadges;

    /**
     * @var BadgeClaim[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Claroline\BadgeBundle\Entity\BadgeClaim", mappedBy="user", cascade={"all"})
     * @ORM\JoinColumn(name="badge_claim_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $badgeClaims;

    public function __construct()
    {
        parent::__construct();
        $this->userMessages      = new ArrayCollection();
        $this->roles             = new ArrayCollection();
        $this->groups            = new ArrayCollection();
        $this->abstractResources = new ArrayCollection();
        $this->salt              = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->orderedTools      = new ArrayCollection();
        $this->userBadges        = new ArrayCollection();
        $this->issuedBadges      = new ArrayCollection();
        $this->badgeClaims       = new ArrayCollection();
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
    public function getSalt()
    {
        return $this->salt;
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
            return;
        }

        $this->password = $password;

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
     * @return Group[]|ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Returns the user's roles (including role's ancestors) as an array
     * of string values (needed for Symfony security checks). The roles
     * owned by groups which the user belong can also be included.
     *
     * @param boolean $areGroupsIncluded
     *
     * @return array[string]
     */
    public function getRoles($areGroupsIncluded = true)
    {
        $roleNames = parent::getRoles();

        if ($areGroupsIncluded) {
            foreach ($this->getGroups() as $group) {
                $roleNames = array_unique(array_merge($roleNames, $group->getRoles()));
            }
        }

        return $roleNames;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;

        return $this;
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if ($user->getRoles() !== $this->getRoles()) {
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
     * @param  string $phone
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
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param string $mail
     *
     * @return User
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

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
     * @return string
     */
    public function serialize()
    {
        return serialize(
            array(
                'id' => $this->id,
                'username' => $this->username,
                'roles' => $this->getRoles()
            )
        );
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        $this->id = $unserialized['id'];
        $this->username = $unserialized['username'];
        $this->rolesStringAsArray = $unserialized['roles'];
        $this->groups = new ArrayCollection();
    }

    /**
     * @param Workspace\AbstractWorkspace $workspace
     *
     * @return User
     */
    public function setPersonalWorkspace($workspace)
    {
        $this->personalWorkspace = $workspace;

        return $this;
    }

    /**
     * @return Workspace\AbstractWorkspace
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
     * Sets the user creation date.
     *
     * NOTE : creation date is already handled by the timestamp listener; this
     *        setter exists mainly for testing purposes.
     *
     * @param \DateTime $date
     */
    public function setCreationDate(\DateTime $date)
    {
        $this->created = $date;
    }

    /**
     * @return mixed
     */
    public function getPlatformRole()
    {
        $roles = $this->getEntityRoles();

        foreach ($roles as $role) {
            if ($role->getType() != Role::WS_ROLE) {
                return $role;
            }
        }
    }

    /**
     * Replace the old platform role of a user by a new one.
     *
     * @param Role $platformRole
     */
    public function setPlatformRole($platformRole)
    {
        $roles = $this->getEntityRoles();

        foreach ($roles as $role) {
            if ($role->getType() != Role::WS_ROLE) {
                $removedRole = $role;
            }
        }

        if (isset($removedRole)) {
            $this->roles->removeElement($removedRole);
        }

        $this->roles->add($platformRole);
    }

    public function getOrderedTools()
    {
        return $this->orderedTools;
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

    /**
     * @param \Claroline\BadgeBundle\Entity\Badge[]|\Doctrine\Common\Collections\ArrayCollection $badges
     *
     * @return User
     */
    public function setUserBadges($badges)
    {
        $this->userBadges = $badges;

        return $this;
    }

    /**
     * @return \Claroline\BadgeBundle\Entity\UserBadge[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getUserBadges()
    {
        return $this->userBadges;
    }

    /**
     * @return \Claroline\BadgeBundle\Entity\Badge[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getBadges()
    {
        $badges = new ArrayCollection();

        foreach ($this->getUserBadges() as $userBadge) {
            $badges[] = $userBadge->getBadge();
        }

        return $badges;
    }

    /**
     * @param Badge $badge
     *
     * @return bool
     */
    public function hasBadge(badge $badge)
    {
        foreach ($this->getBadges() as $userBadge) {
            if ($userBadge->getId() === $badge->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\BadgeClaim[]|\Doctrine\Common\Collections\ArrayCollection $badgeClaims
     *
     * @return User
     */
    public function setBadgeClaims($badgeClaims)
    {
        $this->badgeClaims = $badgeClaims;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\BadgeClaim[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getBadgeClaims()
    {
        return $this->badgeClaims;
    }

    /**
     * @param Badge $badge
     *
     * @return bool
     */
    public function hasClaimedFor(Badge $badge)
    {
        foreach ($this->getBadgeClaims() as $claimedBadge) {
            if ($badge->getId() === $claimedBadge->getId()) {
                return true;
            }
        }

        return false;
    }
}
