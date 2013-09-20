<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 16:06
 */

namespace Icap\DropZoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__dropzonebundle_criterion")
 */
class Criterion {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(name="instruction", type="text", nullable=false)
     */
    protected $instruction;
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Icap\DropZoneBundle\Entity\DropZone",
     *      inversedBy="peerReviewCriteria"
     * )
     * @ORM\JoinColumn(name="drop_zone_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $dropZone;

    /**
     * @return mixed
     */
    public function getDropZone()
    {
        return $this->dropZone;
    }

    /**
     * @param mixed $dropZone
     */
    public function setDropZone($dropZone)
    {
        $this->dropZone = $dropZone;
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
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * @param mixed $instruction
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    }

    /**
     * Proxy dropZone
     * @return mixed
     */
    public function getTotalCriteriaColumn()
    {
        return $this->getDropZone()->getTotalCriteriaColumn();
    }

    /**
     * Proxy dropZone
     * @param mixed
     */
    public function setTotalCriteriaColumn($value)
    {
        $this->getDropZone()->setTotalCriteriaColumn($value);
    }

    /**
     * Proxy dropZone
     * @return mixed
     */
    public function getAllowCommentInCorrection()
    {
        return $this->getDropZone()->getAllowCommentInCorrection() ? 1 : 0;
    }

    /**
     * Proxy dropZone
     * @param mixed
     */
    public function setAllowCommentInCorrection($value)
    {
        $this->getDropZone()->setAllowCommentInCorrection($value == 1);
    }
}