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

use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Model\GroupsTrait;
use Claroline\CoreBundle\Entity\Model\OrganizationsTrait;
use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Validator\Constraints as ClaroAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @ORM\Table(
 *     name="claro_user",
 *     indexes={
 *         @Index(name="code_idx", columns={"administrative_code"}),
 *         @Index(name="enabled_idx", columns={"is_enabled"}),
 *         @Index(name="is_removed", columns={"is_removed"})
 * }
 *
 * )
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @DoctrineAssert\UniqueEntity("username")
 * @DoctrineAssert\UniqueEntity("mail")
 * @Assert\Callback(methods={"isPublicUrlValid"})
 * @ClaroAssert\Username()
 * @ClaroAssert\UserAdministrativeCode()
 */
class User extends AbstractRoleSubject implements Serializable, AdvancedUserInterface, EquatableInterface
{
    use UuidTrait;
    use GroupsTrait;
    use OrganizationsTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_user", "api_organization_tree", "api_organization_list", "api_message", "api_user_min"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", length=50)
     * @Assert\NotBlank()
     * @Groups({"api_user", "api_message", "api_user_min"})
     * @SerializedName("firstName")
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", length=50)
     * @Assert\NotBlank()
     * @Groups({"api_user", "api_message", "api_user_min"})
     * @SerializedName("lastName")
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min="3")
     * @Groups({"api_user", "api_organization_tree", "api_organization_list", "api_user_min"})
     * @SerializedName("username")
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
     * @Groups({"api_user", "api_user_min"})
     * @SerializedName("locale")
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
     * @Groups({"api_user", "api_user_min"})
     * @SerializedName("phone")
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Assert\Email(strict = true)
     * @Groups({"api_user", "api_user_min"})
     * @SerializedName("mail")
     */
    protected $mail;

    /**
     * @var string
     *
     * @ORM\Column(name="administrative_code", nullable=true)
     * @Groups({"api_user", "api_user_min"})
     * @SerializedName("administrativeCode")
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
     * @Groups({"admin"})
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
     * @Groups({"api_user"})
     * @ORM\JoinTable(name="claro_user_role")
     */
    protected $roles;

    /**
     * @var ResourceNode[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     mappedBy="creator"
     * )
     *
     * @todo relation should not be declared here (only use Unidirectional)
     */
    protected $resourceNodes;

    /**
     * @var Workspace\Workspace
     *
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     inversedBy="personalUser",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="workspace_id", onDelete="SET NULL")
     * @Groups({"api_user"})
     */
    protected $personalWorkspace;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Groups({"api_user"})
     * @SerializedName("created")
     */
    protected $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     * @Groups({"api_user"})
     */
    protected $lastLogin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="initialization_date", type="datetime", nullable=true)
     * @Groups({"api_user"})
     * @SerializedName("initDate")
     */
    protected $initDate;

    /**
     * @var OrderedTool[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     mappedBy="user"
     * )
     *
     * @todo relation should not be declared here (only use Unidirectional)
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
     * @ORM\Column(nullable=true)
     * @Groups({"api_user", "api_user_min"})
     * @SerializedName("picture")
     */
    protected $picture;

    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $pictureFile;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"api_user", "api_user_min"})
     * @SerializedName("description")
     */
    protected $description;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"api_user"})
     * @SerializedName("hasAcceptedTerms")
     */
    protected $hasAcceptedTerms;

    /**
     *  This should be renamed because this field really means "is not deleted".
     *
     * @var bool
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     * @Groups({"api_user", "api_user_min"})
     */
    protected $isEnabled = true;

    /**
     * @ORM\Column(name="is_removed", type="boolean")
     * @Groups({"api_user", "api_user_min"})
     */
    protected $isRemoved = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_mail_notified", type="boolean")
     * @Groups({"api_user"})
     * @SerializedName("isMailNotified")
     */
    protected $isMailNotified = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_mail_validated", type="boolean")
     * @Groups({"api_user"})
     * @SerializedName("isMailValidated")
     */
    protected $isMailValidated = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="hide_mail_warning", type="boolean")
     * @Groups({"api_user"})
     * @SerializedName("isMailValidated")
     */
    protected $hideMailWarning = false;

    /**
     * @ORM\Column(name="last_uri", length=255, nullable=true)
     * @Groups({"api_user"})
     * @SerializedName("lastUri")
     */
    protected $lastUri;

    /**
     * @var string
     *
     * @Assert\Regex("/^[^\/]+$/")
     * @ORM\Column(name="public_url", type="string", nullable=true, unique=true)
     * @Groups({"api_user"})
     * @SerializedName("publicUrl")
     */
    protected $publicUrl;

    /**
     * @var bool
     *
     * @ORM\Column(name="has_tuned_public_url", type="boolean")
     * @Groups({"api_user"})
     * @SerializedName("hasTunedPublicUrl")
     */
    protected $hasTunedPublicUrl = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiration_date", type="datetime", nullable=true)
     * @Groups({"api_user"})
     * @SerializedName("expirationDate")
     */
    protected $expirationDate;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacetValue",
     *     mappedBy="user",
     *     cascade={"persist"}
     * )
     * @Groups({"api_user"})
     *
     * @todo relation should not be declared here (only use Unidirectional)
     */
    protected $fieldsFacetValue;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     * @Groups({"api_user"})
     * @SerializedName("authentication")
     */
    protected $authentication;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\UserOptions",
     *     inversedBy="user"
     * )
     * @ORM\JoinColumn(name="options_id", onDelete="SET NULL", nullable=true)
     */
    protected $options;

    /**
     * @ORM\Column(name="email_validation_hash", nullable=true)
     */
    protected $emailValidationHash;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     inversedBy="users"
     * )
     */
    protected $organizations;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Calendar\Event",
     *     mappedBy="user",
     *     cascade={"persist"}
     * )
     */
    protected $events;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization", inversedBy="administrators")
     * @ORM\JoinTable(name="claro_user_administrator")
     * @Groups({"api_user"})
     */
    protected $administratedOrganizations;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Location",
     *     inversedBy="users"
     * )
     */
    protected $locations;

    public function __construct()
    {
        parent::__construct();
        $this->roles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->resourceNodes = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->orderedTools = new ArrayCollection();
        $this->fieldsFacetValue = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->administratedOrganizations = new ArrayCollection();
        $this->refreshUuid();
        $this->setEmailValidationHash(uniqid('', true));
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
            foreach ($this->getGroups() as $group) {
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
            [
                'id' => $this->id,
                'username' => $this->username,
                'roles' => $this->getRoles(),
            ]
        );
    }

    /**
     * @param string $serialized
     *
     * @deprecated should be removed but I don't know if it's used somewhere
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
     *
     * @param \DateTime $date
     */
    public function setCreationDate(\DateTime $date)
    {
        $this->created = $date;
    }

    /**
     * @return mixed
     *
     * @deprecated
     */
    public function getPlatformRole()
    {
        $roles = $this->getEntityRoles();

        foreach ($roles as $role) {
            if (Role::WS_ROLE !== $role->getType()) {
                return $role;
            }
        }
    }

    /**
     * Replace the old platform roles of a user by a new array.
     *
     * @param $platformRoles
     */
    public function setPlatformRoles($platformRoles)
    {
        $roles = $this->getEntityRoles();
        $removedRoles = [];

        foreach ($roles as $role) {
            if (Role::WS_ROLE !== $role->getType()) {
                $removedRoles[] = $role;
            }
        }

        foreach ($removedRoles as $removedRole) {
            $this->roles->removeElement($removedRole);
        }

        foreach ($platformRoles as $platformRole) {
            $this->roles->add($platformRole);
        }
    }

    /**
     * @return OrderedTool[]|ArrayCollection
     *
     * @deprecated
     */
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

    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    public function setPictureFile(UploadedFile $pictureFile)
    {
        $this->pictureFile = $pictureFile;
    }

    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
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

        return ($this->getExpirationDate() >= new \DateTime()) ? true : false;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
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

    public function setLastUri($lastUri)
    {
        $this->lastUri = $lastUri;
    }

    public function getLastUri()
    {
        return $this->lastUri;
    }

    /**
     * @param string $publicUrl
     *
     * @return User
     */
    public function setPublicUrl($publicUrl)
    {
        $this->publicUrl = $publicUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublicUrl()
    {
        return $this->publicUrl;
    }

    /**
     * @param mixed $hasTunedPublicUrl
     *
     * @return User
     */
    public function setHasTunedPublicUrl($hasTunedPublicUrl)
    {
        $this->hasTunedPublicUrl = $hasTunedPublicUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function hasTunedPublicUrl()
    {
        return $this->hasTunedPublicUrl;
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @deprecated should be moved in UserValidator
     */
    public function isPublicUrlValid(ExecutionContextInterface $context)
    {
        // Search for whitespaces
        if (preg_match("/\s/", $this->getPublicUrl())) {
            $context->addViolationAt('publicUrl', 'public_profile_url_not_valid', [], null);
        }
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

    public function getFieldsFacetValue()
    {
        return $this->fieldsFacetValue;
    }

    public function addFieldFacet(FieldFacetValue $fieldFacetValue)
    {
        $this->fieldsFacetValue->add($fieldFacetValue);
    }

    public function setInitDate($initDate)
    {
        $this->initDate = $initDate;
    }

    public function getInitDate()
    {
        return $this->initDate;
    }

    public function setAuthentication($authentication)
    {
        $this->authentication = $authentication;
    }

    public function getAuthentication()
    {
        return $this->authentication;
    }

    public static function getEditableProperties()
    {
        return [
            'username' => false,
            'firstName' => false,
            'lastName' => false,
            'administrativeCode' => false,
            'email' => false,
            'phone' => true,
            'picture' => true,
            'description' => true,
        ];
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(UserOptions $options)
    {
        $this->options = $options;
    }

    public function __toString()
    {
        return $this->firstName.' '.$this->lastName;
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

    public function setHideMailWarning($hideMailWarning)
    {
        $this->hideMailWarning = $hideMailWarning;
    }

    public function getHideMailWarning()
    {
        return $this->hideMailWarning;
    }

    /**
     * @param bool $includedGroups
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

        return array_merge($organizations, $this->organizations->toArray());
    }

    /**
     * @return ArrayCollection
     */
    public function getUserOrganizations()
    {
        return $this->organizations;
    }

    public function getAdministratedOrganizations()
    {
        return $this->administratedOrganizations;
    }

    public function addAdministratedOrganization(Organization $organization)
    {
        $this->administratedOrganizations->add($organization);
    }

    public function removeAdministratedOrganization(Organization $organization)
    {
        $this->administratedOrganizations->removeElement($organization);
    }

    public function setAdministratedOrganizations($organizations)
    {
        $this->administratedOrganizations = $organizations;
    }

    public function setIsRemoved($isRemoved)
    {
        $this->isRemoved = $isRemoved;
    }

    //alias
    public function remove()
    {
        $this->setIsRemoved(true);
    }

    public function getIsRemoved()
    {
        return $this->isRemoved;
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
}
