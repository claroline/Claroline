<?php

namespace Claroline\EvaluationBundle\Entity\UserEvaluation;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\Sequence\Step;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * SequenceProgression
 * Represents the progression of a User in a Step.
 */
#[ORM\Table(name: 'innova_path_progression')]
#[ORM\Entity]
class SequenceProgression
{
    use Id;

    /**
     * Step for which we track the progression.
     */
    #[ORM\ManyToOne(targetEntity: Step::class)]
    #[ORM\JoinColumn(name: 'step_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Step $step;

    /**
     * User for which we track the progression.
     *
     * @deprecated retrieve it from parent evaluation
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(name: 'last_activity_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastActivityAt = null;

    /**
     * Current state of the Step.
     */
    #[ORM\Column(name: 'progression_status', type: Types::STRING)]
    private string $status = EvaluationStatus::NOT_ATTEMPTED;

    /**
     * The parent sequence evaluation.
     */
    #[ORM\ManyToOne(targetEntity: SequenceEvaluation::class)]
    #[ORM\JoinColumn(name: 'sequence_evaluation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?SequenceEvaluation $sequenceEvaluation = null;

    /**
     * The user evaluation for the step activity if any.
     */
    #[ORM\ManyToOne(targetEntity: ResourceEvaluation::class)]
    #[ORM\JoinColumn(name: 'resource_evaluation_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?ResourceEvaluation $resourceEvaluation = null;

    public function getStep(): ?Step
    {
        return $this->step;
    }

    public function setStep(Step $step): void
    {
        $this->step = $step;
    }

    /**
     * @deprecated
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @deprecated
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getLastActivityAt(): ?\DateTimeInterface
    {
        return $this->lastActivityAt;
    }

    public function setLastActivityAt(?\DateTimeInterface $lastActivityAt = null): void
    {
        $this->lastActivityAt = $lastActivityAt;
    }

    public function getSequenceEvaluation(): ?SequenceEvaluation
    {
        return $this->sequenceEvaluation;
    }

    public function setSequenceEvaluation(?SequenceEvaluation $sequenceEvaluation): void
    {
        $this->sequenceEvaluation = $sequenceEvaluation;
    }

    public function getResourceEvaluation(): ?ResourceEvaluation
    {
        return $this->resourceEvaluation;
    }

    public function setResourceEvaluation(?ResourceEvaluation $resourceEvaluation): void
    {
        $this->resourceEvaluation = $resourceEvaluation;
    }
}
