<?php

namespace Claroline\EvaluationBundle\Entity\Parameters;

use Claroline\EvaluationBundle\Entity\Certified;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Doctrine\ORM\Mapping as ORM;

/**
 * Store evaluation parameters for evaluated sequences.
 */
#[ORM\Entity]
#[ORM\Table(name: 'claro_evaluation_sequence_parameters')]
class SequenceParameters extends AbstractEvaluationParameters
{
    use Certified;

    #[ORM\JoinColumn(name: 'sequence_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Sequence::class)]
    private ?Sequence $sequence = null;

    public function getSequence(): ?Sequence
    {
        return $this->sequence;
    }

    public function setSequence(Sequence $sequence): void
    {
        $this->sequence = $sequence;
    }
}
