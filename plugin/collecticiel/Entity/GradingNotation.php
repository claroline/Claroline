<?php
/**
 * Created by : Eric VINCENT
 * Date: 04/2016.
 */

namespace Innova\CollecticielBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Innova\CollecticielBundle\Repository\GradingNotationRepository")
 * @ORM\Table(name="innova_collecticielbundle_grading_notation")
 */
class GradingNotation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="notation_name", type="text", nullable=false)
     */
    protected $notationName;

    /**
     * Lien avec la table Dropzone.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Dropzone",
     *      inversedBy="gradingNotations"
     * )
     * @ORM\JoinColumn(name="dropzone_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $dropzone;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Innova\CollecticielBundle\Entity\ChoiceNotation",
     *     mappedBy="gradingNotation",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $choiceNotations;

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
    public function setDropzone(Dropzone $dropzone)
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
        $this->choiceCriterias = new ArrayCollection();
    }

    /**
     * Add choiceCriteria.
     *
     * @param \Innova\CollecticielBundle\Entity\ChoiceCriteria $choiceCriteria
     *
     * @return GradingCriteria
     */
    public function addChoiceCriteria(ChoiceCriteria $choiceCriteria)
    {
        $this->choiceCriterias[] = $choiceCriteria;

        return $this;
    }

    /**
     * Remove choiceCriteria.
     *
     * @param \Innova\CollecticielBundle\Entity\ChoiceCriteria $choiceCriteria
     */
    public function removeChoiceCriteria(ChoiceCriteria $choiceCriteria)
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

    /**
     * Set notationName.
     *
     * @param string $notationName
     *
     * @return GradingNotation
     */
    public function setNotationName($notationName)
    {
        $this->notationName = $notationName;

        return $this;
    }

    /**
     * Get notationName.
     *
     * @return string
     */
    public function getNotationName()
    {
        return $this->notationName;
    }

    /**
     * Add choiceNotation.
     *
     * @param \Innova\CollecticielBundle\Entity\ChoiceNotation $choiceNotation
     *
     * @return GradingNotation
     */
    public function addChoiceNotation(ChoiceNotation $choiceNotation)
    {
        $this->choiceNotations[] = $choiceNotation;

        return $this;
    }

    /**
     * Remove choiceNotation.
     *
     * @param \Innova\CollecticielBundle\Entity\ChoiceNotation $choiceNotation
     */
    public function removeChoiceNotation(ChoiceNotation $choiceNotation)
    {
        $this->choiceNotations->removeElement($choiceNotation);
    }

    /**
     * Get choiceNotations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChoiceNotations()
    {
        return $this->choiceNotations;
    }
}
