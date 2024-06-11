<?php

namespace Innova\PathBundle\Entity\Path;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\HasEndPage;
use Claroline\CoreBundle\Entity\Resource\HasHomePage;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\EvaluationBundle\Entity\EvaluationFeedbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Innova\PathBundle\Entity\Step;

/**
 * Path resource.
 *
 * @ORM\Entity(repositoryClass="Innova\PathBundle\Repository\PathRepository")
 * @ORM\Table(name="innova_path")
 */
class Path extends AbstractResource
{
    use HasHomePage;
    use HasEndPage;
    use EvaluationFeedbacks;

    /**
     * @ORM\OneToMany(targetEntity="Innova\PathBundle\Entity\Step", mappedBy="path", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private Collection $steps;

    /**
     * Numbering of the steps.
     *
     * @ORM\Column
     */
    private string $numbering = 'none';

    /**
     * Is it possible for the user to manually set the progression.
     *
     * @ORM\Column(name="manual_progression_allowed", type="boolean")
     */
    private bool $manualProgressionAllowed = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_id", nullable=true, onDelete="SET NULL")
     */
    private ?ResourceNode $overviewResource;

    /**
     * Force the opening of secondary resources.
     *
     * @ORM\Column(options={"default" : "_self"})
     */
    private string $secondaryResourcesTarget = '_self';

    /**
     * @ORM\Column(name="score_total", type="float", options={"default" = 100})
     */
    private ?float $scoreTotal = 100;

    /**
     * Score to obtain to pass.
     *
     * @ORM\Column(name="success_score", type="float", nullable=true)
     */
    private ?float $successScore = 50;

    /**
     * @ORM\Column(name="show_score", type="boolean")
     *
     * @deprecated will be replaced by the score type on resource node
     */
    private bool $showScore = false;

    public function __construct()
    {
        parent::__construct();

        $this->steps = new ArrayCollection();
    }

    public function addStep(Step $step): void
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
        }
    }

    public function removeStep(Step $step): void
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
        }
    }

    public function getStep(string $stepId): ?Step
    {
        $found = null;

        foreach ($this->steps as $step) {
            if ($step->getUuid() === $stepId) {
                $found = $step;
                break;
            }
        }

        return $found;
    }

    /** @return Step[] */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function getNumbering(): string
    {
        return $this->numbering;
    }

    public function setNumbering(string $numbering): void
    {
        $this->numbering = $numbering;
    }

    public function isManualProgressionAllowed(): bool
    {
        return $this->manualProgressionAllowed;
    }

    public function setManualProgressionAllowed(bool $manualProgressionAllowed): void
    {
        $this->manualProgressionAllowed = $manualProgressionAllowed;
    }

    /**
     * Get root step of the path.
     */
    public function getRootSteps(): array
    {
        $roots = [];

        if (!empty($this->steps)) {
            foreach ($this->steps as $step) {
                if (null === $step->getParent()) {
                    // Root step found
                    $roots[] = $step;
                }
            }
        }

        return $roots;
    }

    public function getOverviewResource(): ?ResourceNode
    {
        return $this->overviewResource;
    }

    public function setOverviewResource(?ResourceNode $overviewResource = null): void
    {
        $this->overviewResource = $overviewResource;
    }

    /**
     * Get the opening target for secondary resources.
     */
    public function getSecondaryResourcesTarget(): string
    {
        return $this->secondaryResourcesTarget;
    }

    /**
     * Set the opening target for secondary resources.
     */
    public function setSecondaryResourcesTarget(string $secondaryResourcesTarget): void
    {
        $this->secondaryResourcesTarget = $secondaryResourcesTarget;
    }

    public function hasResources(): bool
    {
        if (!empty($this->overviewResource)) {
            return true;
        }

        foreach ($this->steps as $step) {
            if ($step->hasResources()) {
                return true;
            }
        }

        return false;
    }

    public function getScoreTotal(): ?float
    {
        return $this->scoreTotal;
    }

    public function setScoreTotal(?float $scoreTotal = null): void
    {
        $this->scoreTotal = $scoreTotal;
    }

    public function getSuccessScore(): ?float
    {
        return $this->successScore;
    }

    public function setSuccessScore(?float $successScore = null): void
    {
        $this->successScore = $successScore;
    }

    public function getShowScore(): bool
    {
        return $this->showScore;
    }

    public function setShowScore($showScore): void
    {
        $this->showScore = $showScore;
    }
}
