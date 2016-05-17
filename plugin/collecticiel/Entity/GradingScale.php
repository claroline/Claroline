<?php
/**
 * Created by : Eric VINCENT
 * Date: 04/2016.
 */

namespace Innova\CollecticielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Innova\CollecticielBundle\Repository\GradingScaleRepository")
 * @ORM\Table(name="innova_collecticielbundle_grading_scale")
 */
class GradingScale
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="scale_name", type="text", nullable=false)
     */
    protected $scaleName;

    /**
     * Lien avec la table Dropzone.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Dropzone",
     *      inversedBy="gradingScales"
     * )
     * @ORM\JoinColumn(name="dropzone_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $dropzone;

    /**
     * Get id.
     *
     * @return int
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
     * Set scaleName.
     *
     * @param string $scaleName
     *
     * @return GradingScale
     */
    public function setScaleName($scaleName)
    {
        $this->scaleName = $scaleName;

        return $this;
    }

    /**
     * Get scaleName.
     *
     * @return string
     */
    public function getScaleName()
    {
        return $this->scaleName;
    }

    /**
     * @param Dropzone $dropzone
     */
    public function setDropzone($dropzone)
    {
        $this->dropzone = $dropzone;

//        $dropzone->addGradingScale($this);

        return $this;
    }

    /**
     * @return Dropzone
     */
    public function getDropzone()
    {
        return $this->dropzone;
    }

    public function __toString()
    {
        return $this->dropzone();
    }
}
