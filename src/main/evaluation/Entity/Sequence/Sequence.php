<?php

namespace Claroline\EvaluationBundle\Entity\Sequence;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Archived;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\DescriptionHtml;
use Claroline\AppBundle\Entity\Meta\IsPublic;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Meta\Published;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\AppBundle\Entity\Meta\Views;
use Claroline\AppBundle\Entity\Restriction\AccessCode;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\AppBundle\Entity\UserViewCounterInterface;
use Claroline\CoreBundle\Entity\Resource\HasEndPage;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Model\HasWorkspace;
use Claroline\EvaluationBundle\Entity\Certified;
use Claroline\EvaluationBundle\Entity\Evaluated;
use Claroline\EvaluationBundle\Entity\EvaluationFeedbacks;
use Claroline\EvaluationBundle\Finder\SequenceType;
use Claroline\EvaluationBundle\Repository\Sequence\SequenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'innova_path')]
#[ORM\Entity(repositoryClass: SequenceRepository::class)]
#[CrudEntity(finderClass: SequenceType::class)]
class Sequence implements CrudEntityInterface, UserViewCounterInterface
{
    // identifiers
    use Id;
    use Uuid;
    use Code;
    use Name;
    // meta
    use Poster;
    use Description;
    use DescriptionHtml;
    use Creator;
    use CreatedAt;
    use UpdatedAt;
    use Published;
    use Archived;
    use IsPublic;
    use HasWorkspace;
    use Views;
    // restrictions
    use AccessibleFrom;
    use AccessibleUntil;
    use AccessCode;
    // evaluation parameters
    use Evaluated;
    use EvaluationFeedbacks;
    use Certified;
    use HasEndPage;

    /**
     * Numbering of the steps.
     */
    #[ORM\Column]
    private string $numbering = 'none';

    /**
     * Pagination of the sequence.
     *   - none : All the steps are displayed in the same page, one after another.
     *   - step : One page per step + all of its sub steps.
     *   - all  : One page per step.
     */
    #[ORM\Column]
    private string $pagination = 'all';

    #[ORM\JoinColumn(name: 'resource_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private ?ResourceNode $overviewResource = null;

    #[ORM\Column(name: 'objective', type: Types::TEXT, nullable: true)]
    private ?string $objective = null;

    /**
     * Force the opening of secondary resources.
     *
     * @deprecated
     */
    #[ORM\Column(options: ['default' => '_self'])]
    private string $secondaryResourcesTarget = '_self';

    #[ORM\Column(name: 'score_total', type: Types::FLOAT, nullable: true)]
    private ?float $scoreTotal = 100;

    /**
     * @deprecated
     */
    #[ORM\Column(name: 'show_score', type: Types::BOOLEAN)]
    private bool $showScore = false;

    /**
     * The conditions to get a success status for the sequence evaluation.
     * Supported conditions: minimal score, min successful resources, max failed resources.
     */
    #[ORM\Column(name: 'success_condition', type: Types::JSON, nullable: true)]
    private ?array $successCondition = null;

    /**
     * @var Collection<int, Step>
     */
    #[ORM\OneToMany(targetEntity: Step::class, mappedBy: 'path', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $steps;

    /**
     * Who see the sequence and should do it?
     */
    #[ORM\OneToMany(targetEntity: Assignment::class, mappedBy: 'sequence', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $assignments;

    /**
     * Which sequences must be done before unlocking this one?
     */
    #[ORM\OneToMany(targetEntity: Requirement::class, mappedBy: 'sequence', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $requirements;

    public function __construct()
    {
        $this->refreshUuid();

        $this->steps = new ArrayCollection();
        $this->requirements = new ArrayCollection();
        $this->assignments = new ArrayCollection();
    }

    public static function getIdentifiers(): array
    {
        return ['code'];
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

    public function getPagination(): string
    {
        return $this->pagination;
    }

    public function setPagination(string $pagination): void
    {
        $this->pagination = $pagination;
    }

    /**
     * Get root steps of the sequence.
     *
     * @return Step[]
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

    public function getObjective(): ?string
    {
        return $this->objective;
    }

    public function setObjective(?string $objective): void
    {
        $this->objective = $objective;
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

    public function getShowScore(): bool
    {
        return $this->showScore;
    }

    public function setShowScore($showScore): void
    {
        $this->showScore = $showScore;
    }

    public function getSuccessCondition(): ?array
    {
        return $this->successCondition;
    }

    public function setSuccessCondition(?array $successCondition): void
    {
        $this->successCondition = $successCondition;
    }

    public function getRequirements(): Collection
    {
        return $this->requirements;
    }

    public function addRequirement(Requirement $requirement): void
    {
        if (!$this->requirements->contains($requirement)) {
            $this->requirements->add($requirement);
            $requirement->setSequence($this);
        }
    }

    public function removeRequirement(Requirement $requirement): void
    {
        if ($this->requirements->contains($requirement)) {
            $this->requirements->removeElement($requirement);
            $requirement->setSequence(null);
        }
    }

    /**
     * @return Collection|Assignment[]
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function getAssignment(string $assignmentUuid): ?Assignment
    {
        $found = null;

        foreach ($this->assignments as $assignment) {
            if ($assignment->getUuid() === $assignmentUuid) {
                $found = $assignment;
                break;
            }
        }

        return $found;
    }

    public function addAssignment(Assignment $assignment): void
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setSequence($this);
        }
    }

    public function removeAssignment(Assignment $assignment): void
    {
        if ($this->assignments->contains($assignment)) {
            $this->assignments->removeElement($assignment);
            $assignment->setSequence(null);
        }
    }
}
