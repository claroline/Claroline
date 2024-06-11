<?php

namespace Innova\PathBundle\Entity;

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
 *
 * @ORM\Table("innova_step")
 * @ORM\Entity()
 */
class Step
{
    use Id;
    use Uuid;
    use Description;
    use Order;
    use Poster;

    /**
     * @ORM\ManyToOne(targetEntity="Step", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Step $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="Step", mappedBy="parent", cascade={"persist", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private Collection $children;

    /**
     * @ORM\ManyToOne(targetEntity="Innova\PathBundle\Entity\Path\Path", inversedBy="steps")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Path $path = null;

    /**
     * Title of the step.
     *
     * @ORM\Column(name="title", nullable=true)
     */
    private ?string $title = null;

    /**
     * The number of the step (either a number, a literal or a custom label).
     *
     * @ORM\Column(nullable=true)
     */
    private ?string $numbering = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_id", nullable=true, onDelete="SET NULL")
     */
    private ?ResourceNode $resource = null;

    /**
     * @ORM\Column(name="showResourceHeader", type="boolean")
     */
    private bool $showResourceHeader = false;

    /**
     * Secondary resources.
     *
     * @ORM\OneToMany(targetEntity="Innova\PathBundle\Entity\SecondaryResource", mappedBy="step", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private Collection $secondaryResources;

    /**
     * @Gedmo\Slug(fields={"title"}, unique=false, updatable=false)
     * @ORM\Column(length=128)
     */
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
