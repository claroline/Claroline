<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 16:26.
 */

namespace Icap\DropzoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Icap\DropzoneBundle\Repository\GradeRepository")
 * @ORM\Table(name="icap__dropzonebundle_grade", uniqueConstraints={@ORM\UniqueConstraint(name="unique_grade_for_criterion_and_correction", columns={"criterion_id", "correction_id"})})
 */
class Grade
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(name="value", type="smallint", nullable=false)
     */
    protected $value = 0;
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Icap\DropzoneBundle\Entity\Criterion"
     * )
     * @ORM\JoinColumn(name="criterion_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $criterion;
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Icap\DropzoneBundle\Entity\Correction",
     *      inversedBy="grades"
     * )
     * @ORM\JoinColumn(name="correction_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $correction;

    /**
     * @return Criterion
     */
    public function getCriterion()
    {
        return $this->criterion;
    }

    /**
     * @param Criterion $criterion
     */
    public function setCriterion($criterion)
    {
        $this->criterion = $criterion;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
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

    /**
     * @param Correction $correction
     */
    public function setCorrection($correction)
    {
        $this->correction = $correction;
    }
}
