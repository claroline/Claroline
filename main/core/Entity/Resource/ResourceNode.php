<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base entity for all resources.
 *
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceNodeRepository")
 * @ORM\Table(name="claro_resource_node")
 * @Gedmo\Tree(type="materializedPath")
 */
class ResourceNode
{
    /**
     * @var string
     */
    const PATH_SEPARATOR = '`';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"api_resource_node"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $guid;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $license;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modification_date", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $modificationDate;

    /**
     * @var ResourceType
     *
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType",
     *     inversedBy="abstractResources",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="resource_type_id", onDelete="CASCADE", nullable=false)
     */
    protected $resourceType;

    /**
     * @var \Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     inversedBy="resourceNodes",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $creator;

    /**
     * @var ResourceIcon
     *
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceIcon",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $icon;

    /**
     * @var string
     *
     * @Gedmo\TreePathSource
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Serializer\Groups({"api_resource_node"})
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description = null;

    /**
     * @var ResourceNode
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumns({@ORM\JoinColumn(onDelete="CASCADE")})
     */
    protected $parent;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer", nullable=true)
     *
     * @todo this property shouldn't be nullable (is it due to materialized path strategy ?)
     */
    protected $lvl;

    /**
     * @var ArrayCollection|ResourceNode[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     mappedBy="parent",
     * )
     * @ORM\OrderBy({"index" = "ASC"})
     */
    protected $children;

    /**
     * @var ArrayCollection|ResourceShortcut[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceShortcut",
     *     mappedBy="target",
     *     cascade={"remove"}
     * )
     */
    protected $shortcuts;

    /**
     * @var \Claroline\CoreBundle\Entity\Workspace\Workspace
     *
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *      inversedBy="resources"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    protected $workspace;

    /**
     * @var string
     *
     * @Gedmo\TreePath(separator="`")
     * @ORM\Column(length=3000, nullable=true)
     *
     * @todo this property shouldn't be nullable (is it due to materialized path strategy ?)
     */
    protected $path;

    /**
     * @var ArrayCollection|ResourceRights[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceRights",
     *     mappedBy="resourceNode"
     * )
     */
    protected $rights;

    /**
     * @var int
     *
     * @ORM\Column(name="value", nullable=true, type="integer")
     */
    protected $index;

    /**
     * @var string
     *
     * @ORM\Column(name="mime_type", nullable=true)
     */
    protected $mimeType;

    /**
     * @var string
     *
     * @ORM\Column(name="class", length=256)
     */
    protected $class;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="accessible_from", type="datetime", nullable=true)
     */
    protected $accessibleFrom;

    /**
     * @var \DateTime
     * @ORM\Column(name="accessible_until", type="datetime", nullable=true)
     */
    protected $accessibleUntil;

    /**
     * @var string
     */
    private $pathForCreationLog = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="published", type="boolean", options={"default": 1})
     */
    protected $published = true;

    /**
     * @ORM\Column(name="published_to_portal", type="boolean", options={"default": 0})
     */
    protected $publishedToPortal = false;

    /**
     * @var ArrayCollection|\Claroline\CoreBundle\Entity\Log\Log[]
     *
     * @ORM\OneToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\Log\Log",
     *  fetch="EXTRA_LAZY",
     *  mappedBy="resourceNode"
     * )
     */
    protected $logs;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $author;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    protected $active = true;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Facet\FieldFacet",
     *     mappedBy="resourceNode"
     * )
     */
    protected $fields;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $fullscreen = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $closable = false;

    /**
     * @var int
     *
     * @ORM\Column(nullable=false, type="integer")
     */
    protected $closeTarget = 0;

    public function __construct()
    {
        $this->guid = Uuid::uuid4()->toString();
        $this->rights = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->fields = new ArrayCollection();
        $this->shortcuts = new ArrayCollection();
    }

    /**
     * Returns the resource id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Sets the resource id.
     * Required by the ResourceController when it creates a fictional root.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the resource license.
     *
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Sets the resource license.
     *
     * @param string $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * Returns the resource creation date.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Sets the resource creation date.
     *
     * NOTE : creation date is already handled by the timestamp listener; this
     *        setter exists mainly for testing purposes.
     *
     * @param \DateTime $date
     */
    public function setCreationDate(\DateTime $date)
    {
        $this->creationDate = $date;
        $this->modificationDate = $date;
    }

    /**
     * Returns the resource modification date.
     *
     * @return \DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Sets the resource modification date.
     *
     * @param \DateTime $date
     */
    public function setModificationDate(\DateTime $date)
    {
        $this->modificationDate = $date;
    }

    /**
     * Returns the resource type.
     *
     * @return ResourceType
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * Sets the resource type.
     *
     * @param ResourceType
     */
    public function setResourceType(ResourceType $resourceType)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * Returns the resource creator.
     *
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Sets the resource creator.
     *
     * @param User $creator
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;
    }

    /**
     * Returns the children resource instances.
     *
     * @return ArrayCollection|ResourceNode[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Sets the workspace containing the resource instance.
     *
     * @param Workspace $workspace
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * Returns the workspace containing the resource instance.
     *
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Returns the resource icon.
     *
     * @return ResourceIcon
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets the resource icon.
     *
     * @param ResourceIcon $icon
     */
    public function setIcon(ResourceIcon $icon)
    {
        $this->icon = $icon;
    }

    /**
     * Sets the parent resource.
     *
     * @param ResourceNode $parent
     */
    public function setParent(ResourceNode $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Returns the parent resource.
     *
     * @return ResourceNode
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Return the lvl value of the resource in the tree.
     *
     * @return int
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Returns the "raw" path of the resource
     * (the path merge names and ids of all items).
     * Eg.: "Root-1/sub_dir-2/file.txt-3/".
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the path cleaned from its ids.
     * Eg.: "Root/sub_dir/file.txt".
     *
     * @return string
     */
    public function getPathForDisplay()
    {
        return self::convertPathForDisplay($this->path);
    }

    /**
     * Sets the resource name.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException if the name contains the path separator ('/')
     */
    public function setName($name)
    {
        if (strpos(self::PATH_SEPARATOR, $name) !== false) {
            throw new \InvalidArgumentException(
                'Invalid character "'.self::PATH_SEPARATOR.'" in resource name.'
            );
        }

        $this->name = $name;
    }

    /**
     * Returns the resource name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Converts a path for display: remove ids.
     *
     * @param string $path
     *
     * @return string
     */
    public static function convertPathForDisplay($path)
    {
        $pathForDisplay = preg_replace('/-\d+'.self::PATH_SEPARATOR.'/', ' / ', $path);

        if ($pathForDisplay !== null && strlen($pathForDisplay) > 0) {
            $pathForDisplay = substr_replace($pathForDisplay, '', -3);
        }

        return $pathForDisplay;
    }

    /**
     * Returns the resource shortcuts.
     *
     * @return ResourceShortcut[]|ArrayCollection
     */
    public function getShortcuts()
    {
        return $this->shortcuts;
    }

    /**
     * Returns the resource rights.
     *
     * @return ResourceRights[]|ArrayCollection
     */
    public function getRights()
    {
        return $this->rights;
    }

    /**
     * Add rights to the resource.
     *
     * @param ResourceRights $right
     */
    public function addRight(ResourceRights $right)
    {
        $this->rights->add($right);
    }

    /**
     * Returns the resource mime-type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Sets the resource mime-type.
     *
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Returns the resource class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Sets the resource class name.
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Returns the resource accessible from date.
     *
     * @return \DateTime
     */
    public function getAccessibleFrom()
    {
        return $this->accessibleFrom;
    }

    /**
     * Sets the resource accessible from date.
     *
     * @param \DateTime $accessibleFrom
     */
    public function setAccessibleFrom(\DateTime $accessibleFrom = null)
    {
        $this->accessibleFrom = $accessibleFrom;
    }

    /**
     * Returns the resource accessible until date.
     *
     * @return \DateTime
     */
    public function getAccessibleUntil()
    {
        return $this->accessibleUntil;
    }

    /**
     * Sets the resource accessible until date.
     *
     * @param \DateTime $accessibleUntil
     */
    public function setAccessibleUntil(\DateTime $accessibleUntil = null)
    {
        $this->accessibleUntil = $accessibleUntil;
    }

    /**
     * This is required for logging the resource path at the creation.
     * Do not use this function otherwise.
     *
     * @param string $path
     */
    public function setPathForCreationLog($path)
    {
        $this->pathForCreationLog = $path;
    }

    /**
     * This is required for logging the resource path at the creation.
     * Do not use this function otherwise.
     *
     * @return string
     */
    public function getPathForCreationLog()
    {
        return $this->pathForCreationLog;
    }

    /**
     * Add a child resource node.
     *
     * @param ResourceNode $resourceNode
     */
    public function addChild(ResourceNode $resourceNode)
    {
        if (!$this->children->contains($resourceNode)) {
            $this->children->add($resourceNode);
        }
    }

    /**
     * Returns whether the resource is published.
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * Sets the resource published state.
     *
     * @param $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    public function isPublishedToPortal()
    {
        return $this->publishedToPortal;
    }

    public function setPublishedToPortal($publishedToPortal)
    {
        $this->publishedToPortal = $publishedToPortal;
    }

    /**
     * Sets the resource index.
     *
     * @param int $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * Returns the resource index.
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Returns the resource author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets the resource author.
     *
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Returns whether the resource is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Sets the resource active state.
     *
     * @param $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * toString method.
     * used to display the no path in forms.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPathForDisplay();
    }

    /**
     * Sets the resource GUID.
     *
     * @param string $guid
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * Returns the resource GUID.
     *
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    public function getFields()
    {
        return $this->fields->toArray();
    }

    public function addField(FieldFacet $field)
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
        }

        return $this;
    }

    public function removeField(FieldFacet $field)
    {
        if ($this->fields->contains($field)) {
            $this->fields->removeElement($field);
        }

        return $this;
    }

    public function setFullscreen($fullscreen)
    {
        $this->fullscreen = $fullscreen;
    }

    public function getFullscreen()
    {
        return $this->fullscreen;
    }

    public function isFullscreen()
    {
        return $this->getFullscreen();
    }

    public function getClosable()
    {
        return $this->closable;
    }

    public function isClosable()
    {
        return $this->getClosable();
    }

    public function setClosable($closable)
    {
        $this->closable = $closable;
    }

    public function getCloseTarget()
    {
        return $this->closeTarget;
    }

    public function setCloseTarget($closeTarget)
    {
        $this->closeTarget = $closeTarget;
    }
}
