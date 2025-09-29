<?php

namespace Claroline\EvaluationBundle\Entity\Sequence;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\AbstractUserView;
use Claroline\EvaluationBundle\Finder\SequenceViewType;
use Claroline\EvaluationBundle\Repository\Sequence\SequenceViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('claro_evaluation_sequence_view')]
#[ORM\Entity(repositoryClass: SequenceViewRepository::class)]
#[CrudEntity(finderClass: SequenceViewType::class)]
class SequenceView extends AbstractUserView
{
    #[ORM\JoinColumn(name: 'sequence_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Sequence::class)]
    private ?Sequence $sequence = null;

    public function getSubject(): Sequence
    {
        return $this->sequence;
    }

    /** @param Sequence $subject */
    public function setSubject(object $subject): void
    {
        $this->sequence = $subject;
    }
}
