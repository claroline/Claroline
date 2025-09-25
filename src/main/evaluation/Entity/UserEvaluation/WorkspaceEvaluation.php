<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Entity\UserEvaluation;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Finder\WorkspaceEvaluationType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'claro_workspace_evaluation')]
#[CrudEntity(finderClass: WorkspaceEvaluationType::class)]
class WorkspaceEvaluation extends AbstractUserEvaluation
{
    #[ORM\JoinColumn(name: 'workspace_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private ?Workspace $workspace = null;

    /**
     * @var Collection<int, SequenceEvaluation>
     */
    #[ORM\ManyToMany(targetEntity: SequenceEvaluation::class)]
    #[ORM\JoinTable(name: 'claro_workspace_evaluation_sequence')]
    #[ORM\JoinColumn(name: 'workspace_evaluation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'sequence_evaluation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $sequenceEvaluations;

    public function __construct()
    {
        parent::__construct();

        $this->sequenceEvaluations = new ArrayCollection();
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }

    public function isCertified(): bool
    {
        return true;
    }

    public function getScoreMax(): ?float
    {
        if ($this->scoreMax) {
            return $this->scoreMax;
        }

        return $this->workspace?->getScoreTotal();
    }

    public function getEstimatedDuration(): ?int
    {
        return $this->workspace?->getEstimatedDuration() ?? 0;
    }

    public function getSequenceEvaluation(string $sequenceUuid): ?SequenceEvaluation
    {
        $found = null;
        foreach ($this->sequenceEvaluations as $sequenceEvaluation) {
            if ($sequenceEvaluation->getSequence()->getUuid() === $sequenceUuid) {
                $found = $sequenceEvaluation;
                break;
            }
        }

        return $found;
    }

    public function getSequenceEvaluations(): Collection
    {
        return $this->sequenceEvaluations;
    }

    public function addSequenceEvaluation(SequenceEvaluation $sequenceEvaluation): void
    {
        if (!$this->sequenceEvaluations->contains($sequenceEvaluation)) {
            $this->sequenceEvaluations->add($sequenceEvaluation);
        }
    }

    public function removeSequenceEvaluation(SequenceEvaluation $sequenceEvaluation): void
    {
        if ($this->sequenceEvaluations->contains($sequenceEvaluation)) {
            $this->sequenceEvaluations->removeElement($sequenceEvaluation);
        }
    }
}
