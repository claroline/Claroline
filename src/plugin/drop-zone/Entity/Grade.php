<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_dropzonebundle_grade')]
#[ORM\UniqueConstraint(name: 'unique_grade_for_criterion_and_correction', columns: ['criterion_id', 'correction_id'])]
#[ORM\Entity]
class Grade
{
    use Id;
    use Uuid;

    /**
     * @var int
     */
    #[ORM\Column(name: 'grade_value', type: Types::INTEGER, nullable: false)]
    protected $value = 0;

    /**
     *
     * @var Correction
     */
    #[ORM\JoinColumn(name: 'correction_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Correction::class, inversedBy: 'grades')]
    protected ?Correction $correction = null;

    /**
     *
     * @var Criterion
     */
    #[ORM\JoinColumn(name: 'criterion_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Criterion::class)]
    protected ?Criterion $criterion = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return Correction
     */
    public function getCorrection()
    {
        return $this->correction;
    }

    public function setCorrection(Correction $correction)
    {
        $this->correction = $correction;
    }

    /**
     * @return Criterion
     */
    public function getCriterion()
    {
        return $this->criterion;
    }

    public function setCriterion(Criterion $criterion)
    {
        $this->criterion = $criterion;
    }
}
