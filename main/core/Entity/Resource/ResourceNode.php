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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Base entity for all resources.
 *
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceNodeRepository")
 * @ORM\Table(name="claro_resource_node")
 * @Gedmo\Tree(type="materializedPath")
 */
class ResourceNode
{
    // identifiers
    use Id;
    use Uuid;

    // meta
    use Thumbnail;
    use Poster;
    use Description;
    use Creator;

    // restrictions
    use AccessibleFrom;
    use AccessibleUntil;

    /**
     * @var string
     */
    const PATH_SEPARATOR = '`';

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $license;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $creationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modification_date", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $modificationDate;

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
    private $resourceType;

    /**
     * @var ResourceIcon
     *
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceIcon",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @deprecated
     *
     * @todo remove me with migration (was used to store thumbnails in some cases)
     */
    private $icon;

    /**
     * Display resource icon/evaluation when the resource is rendered.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private $showIcon = true;

    /**
     * @var string
     *
     * @Gedmo\TreePathSource
     * @ORM\Column()
     */
    private $name;

    /**
     * Permits to hide resources.
     * For now it's only used in widgets. It should be think more globally.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $hidden = false;

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

    /**
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @todo split IPS & access code into 2 props.
     */
    protected $accesses = [];

    /**
     * @var int
     *
     * @ORM\Column(nullable=false, type="integer", name="views_count", options={"default": 0})
     */
    protected $viewsCount = 0;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    protected $deletable = true;

    public function __construct()
    {
        $this->refreshUuid();

        $this->rights = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->logs = new ArrayCollection();
    }

    public function isHidden()
    {
        return $this->hidden;
    }

    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
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
     *
     * @deprecated
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets the resource icon.
     *
     * @param ResourceIcon $icon
     *
     * @deprecated
     */
    public function setIcon(ResourceIcon $icon = null)
    {
        $this->icon = $icon;
    }

    public function getShowIcon()
    {
        return $this->showIcon;
    }

    public function setShowIcon($showIcon)
    {
        $this->showIcon = $showIcon;
    }

    /**
     * Sets the parent resource.
     *
     * @param ResourceNode $parent
     */
    public function setParent(self $parent = null)
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
        if (false !== strpos(self::PATH_SEPARATOR, $name)) {
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

        if (null !== $pathForDisplay && strlen($pathForDisplay) > 0) {
            $pathForDisplay = substr_replace($pathForDisplay, '', -3);
        }

        return $pathForDisplay;
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
        return $this->resourceType->getClass();
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
    public function addChild(self $resourceNode)
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
     *
     * @deprecated
     */
    public function __toString()
    {
        return $this->getPathForDisplay();
    }

    /**
     * Sets the resource GUID.
     *
     * @param string $guid
     *
     * @deprecated
     */
    public function setGuid($guid)
    {
        $this->uuid = $guid;
    }

    /**
     * Returns the resource GUID.
     *
     * @return string
     *
     * @deprecated
     */
    public function getGuid()
    {
        return $this->uuid;
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

    public function setAllowedIps($ips)
    {
        $this->accesses['ip'] = [
            'activateFilters' => !empty($ips),
            'ips' => $ips,
        ];
    }

    public function getAllowedIps()
    {
        return isset($this->accesses['ip']) ? $this->accesses['ip']['ips'] : [];
    }

    public function getAccessCode()
    {
        return isset($this->accesses['code']) ? $this->accesses['code'] : null;
    }

    public function setAccessCode($code)
    {
        $this->accesses['code'] = $code;
    }

    public function getAccesses()
    {
        return $this->accesses;
    }

    public function setAccesses(array $accesses)
    {
        $this->accesses = $accesses;
    }

    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Gets how many times a resource has been viewed.
     *
     * @return int
     */
    public function getViewsCount()
    {
        return $this->viewsCount;
    }

    /**
     * Adds one unit to the resource view count.
     *
     * @return ResourceNode
     */
    public function addView()
    {
        ++$this->viewsCount;

        return $this;
    }

    /**
     * Checks if the resource node can be deleted.
     *
     * @return bool
     */
    public function isDeletable()
    {
        return $this->deletable;
    }

    /**
     * Sets the deletable option.
     *
     * @param bool $deletable
     */
    public function setDeletable($deletable)
    {
        $this->deletable = $deletable;
    }
}
