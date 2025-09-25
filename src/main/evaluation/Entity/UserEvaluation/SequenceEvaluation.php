<?php

namespace Claroline\EvaluationBundle\Entity\UserEvaluation;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Finder\SequenceEvaluationType;
use Claroline\EvaluationBundle\Repository\UserEvaluation\SequenceEvaluationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_evaluation_sequence_evaluation')]
#[ORM\Entity(repositoryClass: SequenceEvaluationRepository::class)]
#[CrudEntity(finderClass: SequenceEvaluationType::class)]
class SequenceEvaluation extends AbstractUserEvaluation
{
    #[ORM\ManyToOne(targetEntity: Sequence::class)]
    #[ORM\JoinColumn(name: 'sequence_id', onDelete: 'CASCADE')]
    private ?Sequence $sequence = null;

    /**
     * @var Collection<int, SequenceProgression>
     */
    #[ORM\OneToMany(targetEntity: SequenceProgression::class, mappedBy: 'sequenceEvaluation', orphanRemoval: true)]
    private Collection $stepProgressions;

    public function __construct()
    {
        parent::__construct();

        $this->stepProgressions = new ArrayCollection();
    }

    public function getSequence(): ?Sequence
    {
        return $this->sequence;
    }

    public function setSequence(Sequence $sequence): void
    {
        $this->sequence = $sequence;
    }

    /**
     * @deprecated
     */
    public function isRequired(): bool
    {
        if ($this->sequence) {
            return $this->sequence->isRequired();
        }

        return false;
    }

    public function isCertified(): bool
    {
        return $this->sequence->isCertified();
    }

    public function getScoreMax(): ?float
    {
        if ($this->scoreMax) {
            return $this->scoreMax;
        }

        return $this->sequence?->getScoreTotal();
    }

    public function getEstimatedDuration(): ?int
    {
        if ($this->sequence) {
            return $this->sequence->getEstimatedDuration();
        }

        return 0;
    }

    public function getStepProgression(string $stepId): ?SequenceProgression
    {
        $found = null;

        foreach ($this->stepProgressions as $stepProgression) {
            if ($stepProgression->getStep()->getUuid() === $stepId) {
                $found = $stepProgression;
                break;
            }
        }

        return $found;
    }

    public function getStepProgressions(): Collection
    {
        return $this->stepProgressions;
    }

    public function addStepProgression(SequenceProgression $stepProgression): void
    {
        if (!$this->stepProgressions->contains($stepProgression)) {
            $this->stepProgressions->add($stepProgression);
            $stepProgression->setSequenceEvaluation($this);
        }
    }

    public function removeStepProgression(SequenceProgression $stepProgression): void
    {
        if ($this->stepProgressions->contains($stepProgression)) {
            $this->stepProgressions->removeElement($stepProgression);
            $stepProgression->setSequenceEvaluation(null);
        }
    }
}
