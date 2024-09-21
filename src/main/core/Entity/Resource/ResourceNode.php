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

use Doctrine\Common\Collections\Collection;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use DateTime;
use InvalidArgumentException;
use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Hidden;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\DescriptionHtml;
use Claroline\AppBundle\Entity\Meta\Published;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\CoreBundle\Model\HasWorkspace;
use Claroline\EvaluationBundle\Entity\Evaluated;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Base entity for all resources.
 */
#[ORM\Table(name: 'claro_resource_node')]
#[ORM\Entity(repositoryClass: ResourceNodeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Tree(type: 'materializedPath')]
class ResourceNode implements CrudEntityInterface
{
    // identifiers
    use Id;
    use Uuid;
    use Code;
    // meta
    use Thumbnail;
    use Poster;
    use Description;
    use DescriptionHtml;
    use Creator;
    use Published;
    use HasWorkspace;
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
     */
    #[ORM\Column(nullable: true)]
    private $license;

    /**
     * @var DateTimeInterface
     */
    #[ORM\Column(name: 'creation_date', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private $creationDate;

    /**
     * @var DateTimeInterface
     */
    #[ORM\Column(name: 'modification_date', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    private $modificationDate;

    /**
     * @var ResourceType
     */
    #[ORM\JoinColumn(name: 'resource_type_id', onDelete: 'CASCADE', nullable: false)]
    #[ORM\ManyToOne(targetEntity: ResourceType::class)]
    private ?ResourceType $resourceType = null;

    /**
     * Display resource icon/evaluation when the resource is rendered.
     *
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    private $showIcon = true;

    /**
     * @var string
     */
    #[ORM\Column]
    #[Gedmo\TreePathSource]
    private $name;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class, inversedBy: 'children')]
    #[Gedmo\TreeParent]
    protected ?ResourceNode $parent = null;

    /**
     * @todo this property shouldn't be nullable (is it due to materialized path strategy ?)
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Gedmo\TreeLevel]
    protected $lvl;

    /**
     * @var Collection<int, \Claroline\CoreBundle\Entity\Resource\ResourceNode>
     */
    #[ORM\OneToMany(targetEntity: ResourceNode::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['index' => 'ASC'])]
    protected Collection $children;

    /**
     * @var string
     *
     *
     * @todo remove me
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\TreePath(separator: '`')]
    protected $path;

    /**
     * @var string
     *
     * nullable true because it's a new property and migrations/updaters were needed
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected $materializedPath;

    /**
     * @var Collection<int, ResourceRights>
     */
    #[ORM\OneToMany(targetEntity: ResourceRights::class, mappedBy: 'resourceNode', orphanRemoval: true)]
    protected Collection $rights;

    /**
     * @var int
     */
    #[ORM\Column(name: 'value', nullable: true, type: Types::INTEGER)]
    protected $index;

    /**
     * @var string
     */
    #[ORM\Column(name: 'mime_type', nullable: true)]
    protected $mimeType;

    /**
     * @var string
     */
    #[ORM\Column(nullable: true)]
    protected $author;

    /**
     * @var bool
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    protected $active = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    protected $fullscreen = false;

    /**
     * @todo split IPS & access code into 2 props.
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected $accesses = [];

    /**
     * @var int
     */
    #[ORM\Column(nullable: false, type: Types::INTEGER, name: 'views_count', options: ['default' => 0])]
    protected $viewsCount = 0;

    /**
     * @var string
     */
    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
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
    }

    public static function getIdentifiers(): array
    {
        return ['code', 'slug'];
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
     * @return DateTime
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
    public function setCreationDate(DateTime $date)
    {
        $this->creationDate = $date;
        $this->modificationDate = $date;
    }

    /**
     * Returns the resource modification date.
     *
     * @return DateTime
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Sets the resource modification date.
     */
    public function setModificationDate(DateTime $date)
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
     * Sets the resource name.
     *
     * @param string $name
     *
     * @throws InvalidArgumentException if the name contains the path separator ('/')
     */
    public function setName($name)
    {
        if (false !== strpos(self::PATH_SEPARATOR, $name)) {
            throw new InvalidArgumentException('Invalid character "'.self::PATH_SEPARATOR.'" in resource name.');
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
     * Add a child resource node.
     */
    public function addChild(self $resourceNode): void
    {
        if (!$this->children->contains($resourceNode)) {
            $this->children->add($resourceNode);
        }
    }

    public function setIndex(int $index): void
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
     * Returns the ancestors of a resource.
     *
     * @return array[array] An array of resources represented as arrays
     */
    public function getAncestors(): array
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

    #[ORM\PreFlush]
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

        $entityManager = $args->getObjectManager();

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
    private function getOldAncestors(): array
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

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug = null)
    {
        $this->slug = $slug;
    }

}
