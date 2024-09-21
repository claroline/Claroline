<?php

namespace Innova\PathBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Innova\PathBundle\Entity\Path\Path;

/**
 * Step.
 */
#[ORM\Table('innova_step')]
#[ORM\Entity]
class Step
{
    use Id;
    use Uuid;
    use Description;
    use Order;
    use Poster;

    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Step::class, inversedBy: 'children')]
    private ?Step $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Step::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $children;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Path::class, inversedBy: 'steps')]
    private ?Path $path = null;

    /**
     * Title of the step.
     */
    #[ORM\Column(name: 'title', nullable: true)]
    private ?string $title = null;

    /**
     * The number of the step (either a number, a literal or a custom label).
     */
    #[ORM\Column(nullable: true)]
    private ?string $numbering = null;

    #[ORM\JoinColumn(name: 'resource_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private ?ResourceNode $resource = null;

    #[ORM\Column(name: 'showResourceHeader', type: Types::BOOLEAN)]
    private bool $showResourceHeader = false;

    /**
     * Secondary resources.
     */
    #[ORM\OneToMany(targetEntity: SecondaryResource::class, mappedBy: 'step', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $secondaryResources;

    #[ORM\Column(length: 128)]
    #[Gedmo\Slug(fields: ['title'], unique: false, updatable: false)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->refreshUuid();

        $this->children = new ArrayCollection();
        $this->secondaryResources = new ArrayCollection();
    }

    /**
     * @internal use Path::addStep() or Path::removeStep().
     */
    public function setPath(Path $path = null): void
    {
        if (!empty($this->path)) {
            $this->path->removeStep($this);
        }

        $this->path = $path;

        if (!empty($path)) {
            $path->addStep($this);
        }

    }

    public function getPath(): ?Path
    {
        return $this->path;
    }

    public function setParent(Step $parent = null): void
    {
        if ($parent !== $this->parent) {
            $this->parent = $parent;

            if (null !== $parent) {
                $parent->addChild($this);
            }
        }
    }

    public function getParent(): ?Step
    {
        return $this->parent;
    }

    public function hasResources(): bool
    {
        return !empty($this->resource) || !empty($this->secondaryResources);
    }

    /** @return Step[] */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return !empty($this->children) && 0 < $this->children->count();
    }

    public function addChild(Step $step): void
    {
        if (!$this->children->contains($step)) {
            $this->children->add($step);
            $step->setPath($this->path);
            $step->setParent($this);
        }
    }

    public function removeChild(Step $step): void
    {
        if ($this->children->contains($step)) {
            $this->children->removeElement($step);
            $step->setParent(null);
        }
    }

    public function getChild(string $childId): ?Step
    {
        $found = null;

        foreach ($this->children as $step) {
            if ($step->getUuid() === $childId) {
                $found = $step;
                break;
            }
        }

        return $found;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getNumbering(): ?string
    {
        return $this->numbering;
    }

    public function setNumbering(string $numbering = null): void
    {
        $this->numbering = $numbering;
    }

    public function getResource(): ?ResourceNode
    {
        return $this->resource;
    }

    public function setResource(ResourceNode $resource = null): void
    {
        $this->resource = $resource;
    }

    /** @return SecondaryResource[] */
    public function getSecondaryResources(): Collection
    {
        return $this->secondaryResources;
    }

    public function emptySecondaryResources(): void
    {
        $this->secondaryResources->clear();
    }

    public function addSecondaryResource(SecondaryResource $secondaryResource): void
    {
        if (!$this->secondaryResources->contains($secondaryResource)) {
            $this->secondaryResources->add($secondaryResource);
            $secondaryResource->setStep($this);
        }
    }

    public function removeSecondaryResource(SecondaryResource $secondaryResource): void
    {
        if ($this->secondaryResources->contains($secondaryResource)) {
            $this->secondaryResources->removeElement($secondaryResource);
        }
    }

    public function getShowResourceHeader(): bool
    {
        return $this->showResourceHeader;
    }

    public function setShowResourceHeader(bool $showResourceHeader): void
    {
        $this->showResourceHeader = $showResourceHeader;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug = null): void
    {
        $this->slug = $slug;
    }
}
