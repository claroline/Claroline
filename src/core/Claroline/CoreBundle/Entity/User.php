<?php //

namespace Claroline\CoreBundle\Entity;

use \Serializable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Role;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\UserRepository")
 * @ORM\Table(
 *      name="claro_user",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="user_unique_username", columns={"username"})
 *      }
 * )
 * @DoctrineAssert\UniqueEntity("username")
 *
 * @todo implement AdvancedUserInterface
 */
class User extends AbstractRoleSubject implements Serializable, UserInterface, EquatableInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="first_name", type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $lastName;

    /**
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $salt;

    /**
     * @Assert\NotBlank()
     */
    protected $plainPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email(checkMX = false)
     */
    protected $mail;

    /**
     * @ORM\Column(name="administrative_code", type="string", nullable=true)
     */
    protected $administrativeCode;

    /**
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Group",
     *      inversedBy="users"
     * )
     * @ORM\JoinTable(
     *     name="claro_user_group",
     *     joinColumns={
     *         @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *     }
     * )
     */
    protected $groups;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="users", fetch="EXTRA_LAZY"
     * )
     * @ORM\JoinTable(
     *     name="claro_user_role",
     *     joinColumns={
     *         @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *     }
     * )
     */
    protected $roles;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource",
     *     mappedBy="creator"
     * )
     */
    protected $abstractResources;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace",
     *     inversedBy="personalUser",
     *     cascade={"remove"}
     * )
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $personalWorkspace;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="creation_date")
     */
    protected $created;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\UserMessage",
     *     mappedBy="user"
     * )
     */
    protected $userMessages;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     mappedBy="user"
     * )
     */
    protected $orderedTools;

    /**
     * @ORM\Column(name="reset_password", type="string", nullable=true)
     */
    protected $resetPasswordHash;

    /**
     * @ORM\Column(name="hash_time",type="integer", nullable=true)
     */
    protected  $hashTime;

    public function __construct()
    {
        parent::__construct();
        $this->userMessages = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->abstractResources = new ArrayCollection();
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->orderedTools = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setPassword($password)
    {
        if (null === $password) {
            return;
        }

        $this->password = $password;
    }

    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
        $this->password = null;
    }

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
    }

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

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    public function getAdministrativeCode()
    {
        return $this->administrativeCode;
    }

    public function setAdministrativeCode($administrativeCode)
    {
        $this->administrativeCode = $administrativeCode;
    }

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

    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        $this->id = $unserialized['id'];
        $this->username = $unserialized['username'];
        $this->rolesStringAsArray = $unserialized['roles'];
        $this->groups = new ArrayCollection();
    }

    public function setPersonalWorkspace($workspace)
    {
        $this->personalWorkspace = $workspace;
    }

    public function getPersonalWorkspace()
    {
        return $this->personalWorkspace;
    }

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

    public function getHashTime() {
        return $this->hashTime;
    }

    public function setHashTime($hashTime) {
        $this->hashTime = $hashTime;
    }


}
