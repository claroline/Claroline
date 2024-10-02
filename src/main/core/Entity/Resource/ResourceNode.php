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

use Claroline\AppBundle\API\Attribute\CrudEntity;
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
use Claroline\CoreBundle\Finder\ResourceNodeType;
use Claroline\CoreBundle\Model\HasWorkspace;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Claroline\EvaluationBundle\Entity\Evaluated;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Base entity for all resources.
 */
#[ORM\Table(name: 'claro_resource_node')]
#[ORM\Entity(repositoryClass: ResourceNodeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Tree(type: 'materializedPath')]
#[CrudEntity(
    finderClass: ResourceNodeType::class
)]
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

    /**
     * The char used by Gedmo\Tree extension to generate the path of the resource.
     * It cannot be used in the resource name.
     */
    public const PATH_SEPARATOR = '`';

    #[ORM\Column(nullable: true)]
    private ?string $license = null;

    #[ORM\Column(name: 'creation_date', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(name: 'modification_date', type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeInterface $modificationDate = null;

    #[ORM\JoinColumn(name: 'resource_type_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ResourceType::class)]
    private ?ResourceType $resourceType = null;

    /**
     * Display resource icon/evaluation when the resource is rendered.
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    private ?bool $showIcon = true;

    #[ORM\Column]
    #[Gedmo\TreePathSource]
    private ?string $name = null;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class, inversedBy: 'children')]
    #[Gedmo\TreeParent]
    protected ?ResourceNode $parent = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Gedmo\TreeLevel]
    protected ?int $lvl = 0;

    /**
     * @var Collection<int, ResourceNode>
     */
    #[ORM\OneToMany(targetEntity: ResourceNode::class, mappedBy: 'parent')]
    protected Collection $children;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\TreePath(separator: ResourceNode::PATH_SEPARATOR)]
    protected ?string $path = null;

    /**
     * @var Collection<int, ResourceRights>
     */
    #[ORM\OneToMany(targetEntity: ResourceRights::class, mappedBy: 'resourceNode', orphanRemoval: true)]
    protected Collection $rights;

    #[ORM\Column(name: 'mime_type', nullable: true)]
    protected ?string $mimeType = null;

    #[ORM\Column(nullable: true)]
    protected ?string $author = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    protected bool $active = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    protected bool $fullscreen = false;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected ?array $accesses = [];

    #[ORM\Column(name: 'views_count', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    protected int $viewsCount = 0;

    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    private ?string $slug = null;

    /**
     * @deprecated
     */
    private ?AbstractResource $resource = null;

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

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(?string $license): void
    {
        $this->license = $license;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    /**
     * Sets the resource creation date.
     *
     * NOTE : creation date is already handled by the timestamp listener; this
     *        setter exists mainly for testing purposes.
     */
    public function setCreationDate(\DateTime $date): void
    {
        $this->creationDate = $date;
        $this->modificationDate = $date;
    }

    public function getModificationDate(): \DateTimeInterface
    {
        return $this->modificationDate;
    }

    /**
     * Sets the resource modification date.
     */
    public function setModificationDate(\DateTime $date): void
    {
        $this->modificationDate = $date;
    }

    public function getResourceType(): ?ResourceType
    {
        return $this->resourceType;
    }

    public function setResourceType(ResourceType $resourceType): void
    {
        $this->resourceType = $resourceType;
    }

    /**
     * Unmapped field, so we don't have to force flush and fetch the database at node copy for the moment.
     *
     * @deprecated
     */
    public function setResource(AbstractResource $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * @deprecated
     */
    public function getResource(): ?AbstractResource
    {
        return $this->resource;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getShowIcon(): bool
    {
        return $this->showIcon;
    }

    public function setShowIcon(bool $showIcon): void
    {
        $this->showIcon = $showIcon;
    }

    public function setParent(self $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?ResourceNode
    {
        return $this->parent;
    }

    public function getLvl(): ?int
    {
        return $this->lvl;
    }

    /**
     * Returns the "raw" path of the resource
     * (the path merge names and ids of all items).
     * Eg.: "Root-1/sub_dir-2/file.txt-3/".
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRights(): Collection
    {
        return $this->rights;
    }

    public function addRight(ResourceRights $right): void
    {
        if (!$this->rights->contains($right)) {
            $this->rights->add($right);
            $right->setResourceNode($this);
        }
    }

    public function removeRight(ResourceRights $right): void
    {
        if ($this->rights->contains($right)) {
            $this->rights->removeElement($right);
            $right->setResourceNode(null);
        }
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getClass(): ?string
    {
        return $this->resourceType->getClass();
    }

    public function getType(): string
    {
        return $this->resourceType->getName();
    }

    public function addChild(self $resourceNode): void
    {
        if (!$this->children->contains($resourceNode)) {
            $this->children->add($resourceNode);
        }
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function setFullscreen(bool $fullscreen): void
    {
        $this->fullscreen = $fullscreen;
    }

    public function isFullscreen(): bool
    {
        return $this->fullscreen;
    }

    public function setAllowedIps(?array $ips): void
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

    public function setAccessCode(?string $code): void
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

    /**
     * Gets how many times a resource has been viewed.
     */
    public function getViewsCount(): ?int
    {
        return $this->viewsCount;
    }

    /**
     * Adds one unit to the resource view count.
     */
    public function addView(): void
    {
        ++$this->viewsCount;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug = null): void
    {
        $this->slug = $slug;
    }
}
