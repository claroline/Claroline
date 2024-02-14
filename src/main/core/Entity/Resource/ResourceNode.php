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

use Claroline\AppBundle\Entity\Display\Hidden;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Published;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\CoreBundle\Model\HasWorkspace;
use Claroline\EvaluationBundle\Entity\Evaluated;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Base entity for all resources.
 *
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository")
 *
 * @ORM\Table(name="claro_resource_node")
 *
 * @Gedmo\Tree(type="materializedPath")
 */
class ResourceNode
{
    // identifiers
    use Id;
    use Uuid;
    use Code;

    // meta
    use Description;
    use CreatedAt;
    use UpdatedAt;
    use Creator;
    use Published;
    use HasWorkspace;

    // display
    use Hidden;
    use Poster;
    use Thumbnail;

    // restrictions
    use AccessibleFrom;
    use AccessibleUntil;

    // evaluation parameters
    use Evaluated;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $license;

    /**
     * @var ResourceType
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType")
     *
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
     *
     * @ORM\Column()
     */
    private $name;

    /**
     * @var ResourceNode
     *
     * @Gedmo\TreeParent
     *
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     inversedBy="children"
     * )
     *
     * @ORM\JoinColumns({@ORM\JoinColumn(onDelete="CASCADE")})
     */
    protected $parent;

    /**
     * @Gedmo\TreeLevel
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $lvl;

    /**
     * @var ArrayCollection|ResourceNode[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     mappedBy="parent"
     * )
     *
     * @ORM\OrderBy({"index" = "ASC"})
     */
    protected $children;

    /**
     * @Gedmo\TreePath(separator="`")
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $path;

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
     * @ORM\Column(type="json", nullable=true)
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
     *
     * @ORM\OrderBy({"creationDate" = "DESC"})
     *
     * @todo : remove me. this relation should not be bi-directional
     *
     * @var ResourceComment[]|ArrayCollection
     */
    protected $comments;

    /**
     * @Gedmo\Slug(fields={"name"})
     *
     * @ORM\Column(length=128, unique=true)
     *
     * @var string
     */
    private $slug;

    /**
     * @deprecated
     */
    private ?AbstractResource $resource = null;

    public function __construct()
    {
        $this->refreshUuid();

        $this->rights = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->code;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
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
     * Eg.: "Root-1`sub_dir-2`file.txt-3`".
     */
    public function getPath(): ?string
    {
        return $this->path;
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
     */
    public function setActive($active)
    {
        $this->active = $active;
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

    public function getAllowedIps(): array
    {
        return isset($this->accesses['ip']) ? $this->accesses['ip']['ips'] : [];
    }

    public function getAccessCode(): ?string
    {
        return isset($this->accesses['code']) ? $this->accesses['code'] : null;
    }

    public function setAccessCode($code): void
    {
        $this->accesses['code'] = $code;
    }

    public function getAccesses(): ?array
    {
        return $this->accesses;
    }

    public function setAccesses(array $accesses): void
    {
        $this->accesses = $accesses;
    }

    public function getViews(): int
    {
        return $this->viewsCount;
    }

    public function addView(): void
    {
        ++$this->viewsCount;
    }

    /**
     * @deprecated
     */
    public function isCommentsActivated(): bool
    {
        return $this->commentsActivated;
    }

    /**
     * @deprecated
     */
    public function setCommentsActivated(bool $commentsActivated): void
    {
        $this->commentsActivated = $commentsActivated;
    }

    /**
     * @deprecated
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @deprecated
     */
    public function addComment(ResourceComment $comment)
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }
    }

    /**
     * @deprecated
     */
    public function removeComment(ResourceComment $comment)
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
        }
    }

    /**
     * @deprecated
     */
    public function emptyComments()
    {
        $this->comments->clear();
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug = null): void
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
