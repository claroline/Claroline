<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 16:06.
 */

namespace Icap\DropzoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__dropzonebundle_criterion")
 */
class Criterion
{
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
     *      targetEntity="Icap\DropzoneBundle\Entity\Dropzone",
     *      inversedBy="peerReviewCriteria"
     * )
     * @ORM\JoinColumn(name="drop_zone_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $dropzone;

    /**
     * @return Dropzone
     */
    public function getDropzone()
    {
        return $this->dropzone;
    }

    /**
     * @param Dropzone $dropzone
     */
    public function setDropzone($dropzone)
    {
        $this->dropzone = $dropzone;
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
     * Proxy dropzone.
     *
     * @return mixed
     */
    public function getTotalCriteriaColumn()
    {
        return $this->getDropzone()->getTotalCriteriaColumn();
    }

    /**
     * Proxy dropzone.
     *
     * @param mixed
     */
    public function setTotalCriteriaColumn($value)
    {
        $this->getDropzone()->setTotalCriteriaColumn($value);
    }

    /**
     * Proxy dropzone.
     *
     * @return mixed
     */
    public function getAllowCommentInCorrection()
    {
        return $this->getDropzone()->getAllowCommentInCorrection() ? 1 : 0;
    }

    /**
     * Proxy dropzone.
     *
     * @param mixed
     */
    public function setAllowCommentInCorrection($value)
    {
        $this->getDropzone()->setAllowCommentInCorrection($value == 1);
    }
}
