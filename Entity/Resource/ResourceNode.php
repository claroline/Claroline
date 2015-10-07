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

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
    const PATH_SEPARATOR = '`';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $license;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="modification_date", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $modificationDate;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType",
     *     inversedBy="abstractResources",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="resource_type_id", onDelete="CASCADE", nullable=false)
     */
    protected $resourceType;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     inversedBy="resourceNodes",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $creator;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceIcon",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $icon;

    /**
     * @Gedmo\TreePathSource
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $name;

    /**
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
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     mappedBy="parent",
     * )
     * @ORM\OrderBy({"index" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceShortcut",
     *     mappedBy="target",
     *     cascade={"remove"}
     * )
     */
    protected $shortcuts;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *      inversedBy="resources"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    protected $workspace;

    /**
     * @Gedmo\TreePath(separator="`")
     * @ORM\Column(length=3000, nullable=true)
     *
     * @todo this property shouldn't be nullable (is it due to materialized path strategy ?)
     */
    protected $path;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceRights",
     *     mappedBy="resourceNode"
     * )
     */
    protected $rights;

    /**
     * @ORM\Column(name="value", nullable=true, type="integer")
     */
    protected $index;

    /**
     * @ORM\Column(name="mime_type", nullable=true)
     */
    protected $mimeType;

    /**
     * @ORM\Column(name="class", length=256)
     */
    protected $class;

    /**
     * @ORM\Column(name="accessible_from", type="datetime", nullable=true)
     */
    protected $accessibleFrom;

    /**
     * @ORM\Column(name="accessible_until", type="datetime", nullable=true)
     */
    protected $accessibleUntil;

    private $pathForCreationLog = '';

    /**
     * @ORM\Column(name="published", type="boolean", options={"default": 1})
     */
    protected $published = true;

    /**
     * @ORM\OneToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\Log\Log",
     *  fetch="EXTRA_LAZY",
     *  mappedBy="resourceNode"
     * )
     */
    protected $logs;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $author;

    /**
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    protected $active = true;

    public function __construct()
    {
        $this->rights = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->logs = new ArrayCollection();
    }

    /**
     * Returns the resource id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the resource id.
     * Required by the ResourceController when it creates a fictionnal root
     *
     * @param integer $id
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

    public function setModificationDate(\DateTime $date)
    {
        $this->modificationDate = $date;
    }

    /**
     * Returns the resource type.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceType
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * Sets the resource type.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType
     */
    public function setResourceType(ResourceType $resourceType)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * Returns the resource creator.
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Sets the resource creator.
     *
     * @param \Claroline\CoreBundle\Entity\User
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;
    }

    /**
     * Returns the children resource instances.
     *
     * @return \Doctrine\Common\ArrayCollection|ResourceNode[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Sets the workspace containing the resource instance.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * Returns the workspace containing the resource instance.
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Sets the parent resource.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $parent
     */
    public function setParent(ResourceNode $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Returns the parent resource.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\AbstractResource
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Return the lvl value of the resource in the tree.
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Returns the "raw" path of the resource
     * (the path merge names and ids of all items).
     * Eg.: "Root-1/subdir-2/file.txt-3/"
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the path cleaned from its ids.
     * Eg.: "Root/subdir/file.txt"
     * @return
     */
    public function getPathForDisplay()
    {
        return self::convertPathForDisplay($this->path);
    }

    /**
     * Sets the resource name.
     *
     * @param  string $name
     * @throws an     exception if the name contains the path separator ('/').
     */
    public function setName($name)
    {
        if (strpos(self::PATH_SEPARATOR, $name) !== false) {
            throw new \InvalidArgumentException(
                'Invalid character "' . self::PATH_SEPARATOR . '" in resource name.'
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
     * Convert a path for display: remove ids.
     *
     * @param string $path
     *
     * @return string
     */
    public static function convertPathForDisplay($path)
    {
        $pathForDisplay = preg_replace('/-\d+' . self::PATH_SEPARATOR . '/', ' / ', $path);

        if ($pathForDisplay !== null && strlen($pathForDisplay) > 0) {
            $pathForDisplay = substr_replace($pathForDisplay, "", -3);
        }

        return $pathForDisplay;
    }

    public function getShortcuts()
    {
        return $this->shortcuts;
    }

    public function getRights()
    {
        return $this->rights;
    }

    public function addRight(ResourceRights $right)
    {
        $this->rights->add($right);
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }
    public function getAccessibleFrom()
    {
        return $this->accessibleFrom;
    }

    public function setAccessibleFrom($accessibleFrom)
    {
        $this->accessibleFrom = $accessibleFrom;
    }

    public function getAccessibleUntil()
    {
        return $this->accessibleUntil;
    }

    public function setAccessibleUntil($accessibleUntil)
    {
        $this->accessibleUntil = $accessibleUntil;
    }

    /**
     * This is required for logging the resource path at the creation.
     * Do not use this function otherwise.
     *
     * @return type
     */
    public function setPathForCreationLog($path)
    {
        $this->pathForCreationLog = $path;
    }

    /**
     * This is required for logging the resource path at the creation.
     * Do not use this function otherwise.
     *
     * @return type
     */
    public function getPathForCreationLog()
    {
        return $this->pathForCreationLog;
    }

    public function addChild(ResourceNode $resourceNode)
    {
        if (!$this->children->contains($resourceNode)) {
            $this->children->add($resourceNode);
        }
    }

    public function isPublished()
    {
        return $this->published;
    }

    public function setPublished($published)
    {
        $this->published = $published;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * toString method.
     * used to display the no path in forms
     */
    public function __toString()
    {
        return $this->getPathForDisplay();
    }
}
