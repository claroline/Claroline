<?php
/**
 * Created by : Eric VINCENT
 * Date: 04/2016.
 */

namespace Innova\CollecticielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Innova\CollecticielBundle\Repository\GradingCriteriaRepository")
 * @ORM\Table(name="innova_collecticielbundle_grading_criteria")
 */
class GradingCriteria
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="criteria_name", type="text", nullable=false)
     */
    protected $criteriaName;

    /**
     * Lien avec la table Dropzone.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Dropzone",
     *      inversedBy="gradingCriterias"
     * )
     * @ORM\JoinColumn(name="dropzone_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $dropzone;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Innova\CollecticielBundle\Entity\ChoiceCriteria",
     *     mappedBy="gradingCriteria",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $choiceCriterias;

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
     * Set criteriaName.
     *
     * @param string $criteriaName
     *
     * @return GradingCriteria
     */
    public function setCriteriaName($criteriaName)
    {
        $this->criteriaName = $criteriaName;

        return $this;
    }

    /**
     * Get criteriaName.
     *
     * @return string
     */
    public function getCriteriaName()
    {
        return $this->criteriaName;
    }

    /**
     * Set dropzone.
     *
     * @param \Innova\CollecticielBundle\Entity\Dropzone $dropzone
     *
     * @return GradingCriteria
     */
    public function setDropzone(\Innova\CollecticielBundle\Entity\Dropzone $dropzone)
    {
        $this->dropzone = $dropzone;

        return $this;
    }

    /**
     * Get dropzone.
     *
     * @return \Innova\CollecticielBundle\Entity\Dropzone
     */
    public function getDropzone()
    {
        return $this->dropzone;
    }
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->choiceCriterias = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add choiceCriteria.
     *
     * @param \Innova\CollecticielBundle\Entity\ChoiceCriteria $choiceCriteria
     *
     * @return GradingCriteria
     */
    public function addChoiceCriteria(\Innova\CollecticielBundle\Entity\ChoiceCriteria $choiceCriteria)
    {
        $this->choiceCriterias[] = $choiceCriteria;

        return $this;
    }

    /**
     * Remove choiceCriteria.
     *
     * @param \Innova\CollecticielBundle\Entity\ChoiceCriteria $choiceCriteria
     */
    public function removeChoiceCriteria(\Innova\CollecticielBundle\Entity\ChoiceCriteria $choiceCriteria)
    {
        $this->choiceCriterias->removeElement($choiceCriteria);
    }

    /**
     * Get choiceCriterias.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChoiceCriterias()
    {
        return $this->choiceCriterias;
    }
}
