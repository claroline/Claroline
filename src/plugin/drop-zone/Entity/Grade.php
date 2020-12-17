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

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_dropzonebundle_grade",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="unique_grade_for_criterion_and_correction", columns={"criterion_id", "correction_id"})
 *     }
 * )
 */
class Grade
{
    use Uuid;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="grade_value", type="integer", nullable=false)
     *
     * @var int
     */
    protected $value = 0;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Correction",
     *     inversedBy="grades"
     * )
     * @ORM\JoinColumn(name="correction_id", nullable=false, onDelete="CASCADE")
     *
     * @var Correction
     */
    protected $correction;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\DropZoneBundle\Entity\Criterion")
     * @ORM\JoinColumn(name="criterion_id", nullable=false, onDelete="CASCADE")
     *
     * @var Criterion
     */
    protected $criterion;

    /**
     * Grade constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
