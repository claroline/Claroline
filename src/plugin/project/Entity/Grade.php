<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ProjectBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_project_grade')]
#[ORM\UniqueConstraint(columns: ['criterion_id', 'correction_id'])]
#[ORM\Entity]
class Grade
{
    use Id;
    use Uuid;

    /**
     * The correction this grade belongs to.
     */
    #[ORM\ManyToOne(targetEntity: Correction::class, inversedBy: 'grades')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Correction $correction = null;

    /**
     * The criterion being scored.
     */
    #[ORM\ManyToOne(targetEntity: Criterion::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Criterion $criterion = null;

    /**
     * Score assigned by the trainer for this criterion.
     * Must be between 0 and the criterion's scoreMax.
     */
    #[ORM\Column(type: Types::FLOAT)]
    private float $value = 0;

    /**
     * Optional feedback from the trainer specific to this criterion.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getCorrection(): ?Correction
    {
        return $this->correction;
    }

    public function setCorrection(?Correction $correction): void
    {
        $this->correction = $correction;
    }

    public function getCriterion(): ?Criterion
    {
        return $this->criterion;
    }

    public function setCriterion(?Criterion $criterion): void
    {
        $this->criterion = $criterion;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): void
    {
        $this->value = $value;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }
}
