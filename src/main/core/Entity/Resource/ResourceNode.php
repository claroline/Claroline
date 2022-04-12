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
use Claroline\AppBundle\Entity\Meta\Published;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\AppBundle\Entity\Restriction\Hidden;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\Evaluated;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Base entity for all resources.
 *
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository")
 * @ORM\Table(name="claro_resource_node")
 * @Gedmo\Tree(type="materializedPath")
 * @ORM\HasLifecycleCallbacks
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
    use Published;
    // restrictions
    use Hidden;
    use AccessibleFrom;
    use AccessibleUntil;
    // evaluation parameters
    use Evaluated;
    const PATH_SEPARATOR = '/';
    const PATH_OLDSEPARATOR = '`';

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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType")
     * @ORM\JoinColumn(name="resource_type_id", onDelete="CASCADE", nullable=false)
     */
    private $resourceType;

    /**
     * Display resource title when the resource is rendered.
     *
     * @ORM\Column(type="boolean", options={"default"=1})
     */
    private $showTitle = true;

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
     *     mappedBy="parent"
     * )
     * @ORM\OrderBy({"index" = "ASC"})
     */
    protected $children;

    /**
     * The parent workspace of the resource.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     *
     * @var Workspace
     */
    protected $workspace;

    /**
     * @var string
     *
     * @Gedmo\TreePath(separator="`")
     * @ORM\Column(length=3000, nullable=true)
     *
     * @todo remove me
     */
    protected $path;

    /**
     * @var string
     *
     * nullable true because it's a new property and migrations/updaters were needed
     * @ORM\Column(length=3000, nullable=true)
     */
    protected $materializedPath;

    /**
     * @var ArrayCollection|ResourceRights[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceRights",
     *     mappedBy="resourceNode",
     *     orphanRemoval=true
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
     * @var ArrayCollection|\Claroline\CoreBundle\Entity\Log\Log[]
     *
     * @ORM\OneToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\Log\Log",
     *  fetch="EXTRA_LAZY",
     *  mappedBy="resourceNode"
     * )
     *
     * @todo : remove me. this relation should not be bi-directional
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

    /**
     * @var bool
     *
     * @ORM\Column(name="comments_activated", type="boolean")
     *
     * @todo : remove me. Should not be here
     */
    protected $commentsActivated = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceComment",
     *     mappedBy="resourceNode"
     * )
     * @ORM\OrderBy({"creationDate" = "DESC"})
     *
     * @todo : remove me. this relation should not be bi-directional
     *
     * @var ResourceComment[]|ArrayCollection
     */
    protected $comments;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     *
     * @var string
     */
    private $slug;

    /**
     * @var AbstractResource
     *
     * @deprecated
     */
    private $resource;

    public function __construct()
    {
        $this->refreshUuid();

        $this->rights = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
     * Unmapped field so we don't have to force flush and fetch the database at node copy for the moment.
     *
     * @param AbstractResource
     *
     * @deprecated
     */
    public function setResource(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return AbstractResource
     *
     * @deprecated
     */
    public function getResource()
    {
        return $this->resource;
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
        $pathForDisplay = preg_replace('/%([^\/]+)\//', ' / ', $this->path);

        if (null !== $pathForDisplay && strlen($pathForDisplay) > 0) {
            $pathForDisplay = substr_replace($pathForDisplay, '', -3);
        }

        return $pathForDisplay;
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
            throw new \InvalidArgumentException('Invalid character "'.self::PATH_SEPARATOR.'" in resource name.');
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
     */
    public function addRight(ResourceRights $right)
    {
        if (!$this->rights->contains($right)) {
            $this->rights->add($right);
            $right->setResourceNode($this);
        }
    }

    /**
     * Remove rights from the resource.
     */
    public function removeRight(ResourceRights $right)
    {
        if ($this->rights->contains($right)) {
            $this->rights->removeElement($right);
            $right->setResourceNode(null);
        }
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
     */
    public function getClass(): ?string
    {
        return $this->resourceType->getClass();
    }

    /**
     * Returns the resource type name.
     */
    public function getType(): string
    {
        return $this->resourceType->getName();
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
     */
    public function addChild(self $resourceNode)
    {
        if (!$this->children->contains($resourceNode)) {
            $this->children->add($resourceNode);
        }
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

    /**
     * Returns the ancestors of a resource.
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function getAncestors()
    {
        // No need to access DB to get ancestors as they are given by the materialized path.
        //I use \/ instead of PATH_SEPARATOR for escape purpose
        $parts = preg_split('/%([^\/]+)\//', $this->materializedPath, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $ancestors = [];
        $countAncestors = count($parts);
        for ($i = 0; $i < $countAncestors; $i += 2) {
            if (array_key_exists($i + 1, $parts)) {
                $ancestors[] = [
                  'id' => $parts[$i + 1], // retro-compatibility
                  'slug' => $parts[$i + 1],
                  'name' => $parts[$i],
              ];
            }
        }

        return $ancestors;
    }

    /**
     * @ORM\PreFlush
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        $ancestors = $this->getOldAncestors();
        $ids = array_map(function ($ancestor) {
            return $ancestor['id'];
        }, $ancestors);
        $ids = array_unique($ids);

        if (count($ids) !== count($ancestors)) {
            return;
        }

        $entityManager = $args->getEntityManager();

        $this->materializedPath = $this->makePath($this);
        $entityManager->persist($this);
    }

    private function makePath(self $node, $path = '')
    {
        if ($node->getParent()) {
            $path = $this->makePath($node->getParent(), $node->getName().'%'.$node->getSlug().self::PATH_SEPARATOR.$path);
        } else {
            $path = $node->getName().'%'.$node->getSlug().self::PATH_SEPARATOR.$path;
        }

        return $path;
    }

    /**
     * Returns the ancestors of a resource.
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function getOldAncestors()
    {
        // No need to access DB to get ancestors as they are given by the materialized path.
        $parts = preg_split('/-(\d+)'.ResourceNode::PATH_OLDSEPARATOR.'/', $this->path, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $ancestors = [];
        $countAncestors = count($parts);
        for ($i = 0; $i < $countAncestors; $i += 2) {
            if (array_key_exists($i + 1, $parts)) {
                $ancestors[] = [
                    'id' => $parts[$i + 1], // retro-compatibility
                    'slug' => $parts[$i + 1],
                    'name' => $parts[$i],
                ];
            }
        }

        return $ancestors;
    }

    /**
     * @return bool
     */
    public function isCommentsActivated()
    {
        return $this->commentsActivated;
    }

    /**
     * @param bool $commentsActivated
     */
    public function setCommentsActivated($commentsActivated)
    {
        $this->commentsActivated = $commentsActivated;
    }

    /**
     * Get comments.
     *
     * @return ResourceComment[]|ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add comment.
     */
    public function addComment(ResourceComment $comment)
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }
    }

    /**
     * Remove comment.
     */
    public function removeComment(ResourceComment $comment)
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
        }
    }

    /**
     * Remove all comments.
     */
    public function emptyComments()
    {
        $this->comments->clear();
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug = null)
    {
        $this->slug = $slug;
    }

    public function getShowTitle(): bool
    {
        return $this->showTitle;
    }

    public function setShowTitle(bool $showTitle)
    {
        $this->showTitle = $showTitle;
    }
}
