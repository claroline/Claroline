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

use Claroline\CoreBundle\Entity\Facet\PanelFacetRole;
use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use RuntimeException;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\RoleRepository")
 * @ORM\Table(name="claro_role")
 * @ORM\HasLifecycleCallbacks
 * @DoctrineAssert\UniqueEntity("name")
 */
class Role implements RoleInterface
{
    use UuidTrait;

    const PLATFORM_ROLE = 1;
    const WS_ROLE = 2;
    const CUSTOM_ROLE = 3;
    const USER_ROLE = 4;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_user", "api_facet_admin", "api_role"})
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Groups({"api_user", "api_facet_admin", "api_role"})
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="translation_key")
     * @Assert\NotBlank()
     * @Groups({"api_role", "api_user", "api_facet_admin"})
     *
     * @var string
     */
    protected $translationKey;

    /**
     * @ORM\Column(name="is_read_only", type="boolean")
     *
     * @var bool
     */
    protected $isReadOnly = false;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     mappedBy="roles"
     * )
     */
    protected $users;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\Facet",
     *     mappedBy="roles"
     * )
     */
    protected $facets;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\PanelFacetRole",
     *     mappedBy="role"
     * )
     */
    protected $panelFacetsRole;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\GeneralFacetPreference",
     *     mappedBy="role"
     * )
     */
    protected $generalFacetPreference;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\AdminTool",
     *     mappedBy="roles"
     * )
     */
    protected $adminTools;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Group",
     *     mappedBy="roles"
     * )
     */
    protected $groups;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"api_user", "api_role"})
     *
     * @var int
     */
    protected $type = self::PLATFORM_ROLE;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceRights",
     *     mappedBy="role"
     * )
     */
    protected $resourceRights;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     inversedBy="roles"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    protected $maxUsers;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\ToolRights",
     *     mappedBy="role"
     * )
     */
    protected $toolRights;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\PwsToolConfig",
     *     mappedBy="role"
     * )
     */
    protected $pwsToolConfig;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\PwsRightsManagementAccess",
     *     mappedBy="role"
     * )
     */
    protected $pwsRightsManagementAccess;

    /**
     * @ORM\Column(name="personal_workspace_creation_enabled", type="boolean")
     *
     * @var bool
     */
    protected $personalWorkspaceCreationEnabled = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\ProfileProperty",
     *     mappedBy="role"
     * )
     */
    protected $profileProperties;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->resourceContext = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->facets = new ArrayCollection();
        $this->panelFacetsRole = new ArrayCollection();
        $this->toolRights = new ArrayCollection();
        $this->pwsToolConfig = new ArrayCollection();
        $this->profileProperties = new ArrayCollection();
        $this->refreshUuid();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the role name. The name must be prefixed by 'ROLE_'. Note that
     * platform-wide roles (as listed in Claroline/CoreBundle/Security/PlatformRoles)
     * cannot be modified by this setter.
     *
     * @param string $name
     *
     * @throws \RuntimeException if the name isn't prefixed by 'ROLE_' or if the role is platform-wide
     */
    public function setName($name)
    {
        if (0 !== strpos($name, 'ROLE_')) {
            throw new RuntimeException('Role names must start with "ROLE_"');
        }

        if (PlatformRoles::contains($this->name)) {
            throw new RuntimeException('Platform roles cannot be modified');
        }

        if (PlatformRoles::contains($name)) {
            $this->isReadOnly = true;
        }

        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setTranslationKey($key)
    {
        $this->translationKey = $key;
    }

    public function getTranslationKey()
    {
        return $this->translationKey;
    }

    public function isReadOnly()
    {
        return $this->isReadOnly;
    }

    /**
     * Alias of getName().
     *
     * @return string The role name
     */
    public function getRole()
    {
        return $this->getName();
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove()
    {
        if (PlatformRoles::contains($this->name)) {
            throw new RuntimeException('Platform roles cannot be deleted');
        }
    }

    public function setReadOnly($value)
    {
        $this->isReadOnly = $value;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function addUser($user)
    {
        $this->users->add($user);

        if ($user->hasRole($this)) {
            $user->addRole($this);
        }
    }

    public function initUsers()
    {
        $this->users = new ArrayCollection();
    }

    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @todo check the type is a Role class constant
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function addResourceRights(ResourceRights $rc)
    {
        $this->resourceRights->add($rc);
    }

    public function getResourceRights()
    {
        return $this->resourceRights;
    }

    public function setWorkspace(Workspace $ws = null)
    {
        $this->workspace = $ws;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    public function getMaxUsers()
    {
        //2147483647 is the maximium integer in the database field.
        return ($this->maxUsers === null) ? 2147483647 : $this->maxUsers;
    }

    public function addToolRights(ToolRights $tr)
    {
        $this->toolRights->add($tr);
    }

    public function getToolRights()
    {
        return $this->toolRights;
    }

    public function addPwsToolConfig(PwsToolConfig $tr)
    {
        $this->pwsToolConfig->add($tr);
    }

    public function getPwsToolConfig()
    {
        return $this->pwsToolConfig;
    }

    public function getPersonalWorkspaceCreationEnabled()
    {
        return $this->personalWorkspaceCreationEnabled;
    }

    public function isPersonalWorkspaceCreationEnabled()
    {
        return $this->personalWorkspaceCreationEnabled;
    }

    public function setPersonalWorkspaceCreationEnabled($boolean)
    {
        $this->personalWorkspaceCreationEnabled = $boolean;
    }

    public function getProfileProperties()
    {
        return $this->profileProperties;
    }

    public function addProfileProperty(ProfileProperty $property)
    {
        $this->profileProperties->add($property);
    }

    public function __toString()
    {
        $name = $this->workspace ? '['.$this->workspace->getName().'] '.$this->name : $this->name;

        return "[{$this->getId()}]".$name;
    }

    public function addPanelFacetRole(PanelFacetRole $pfr)
    {
        $this->panelFacetsRole->add($pfr);
    }

    public function getIsReadOnly()
    {
        return $this->isReadOnly;
    }
}
