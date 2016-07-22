<?php
/**
 * Created by : Eric VINCENT
 * Date: 06/2016.
 */

namespace Innova\CollecticielBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Innova\CollecticielBundle\Repository\ChoiceNotationRepository")
 * @ORM\Table(name="innova_collecticielbundle_choice_notation")
 */
class ChoiceNotation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Lien avec la table GradingNotation.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\GradingNotation",
     *      inversedBy="choiceNotations"
     * )
     * @ORM\JoinColumn(name="criteria_notation_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $gradingNotation;

    /**
     * Lien avec la table Notation.
     */
    /**
     * @ORM\ManyToOne(
     *      targetEntity="Innova\CollecticielBundle\Entity\Notation",
     *      inversedBy="choiceCriterias"
     * )
     * @ORM\JoinColumn(name="notation_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $notation;

    /**
     * @ORM\Column(name="choice_text",type="text", nullable=true)
     */
    protected $choiceText = null;

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
     * Set choiceText.
     *
     * @param string $choiceText
     *
     * @return ChoiceCriteria
     */
    public function setChoiceText($choiceText)
    {
        $this->choiceText = $choiceText;

        return $this;
    }

    /**
     * Get choiceText.
     *
     * @return string
     */
    public function getChoiceText()
    {
        return $this->choiceText;
    }

    /**
     * Set gradingCriteria.
     *
     * @param GradingCriteria $gradingCriteria
     *
     * @return ChoiceCriteria
     */
    public function setGradingCriteria(GradingCriteria $gradingCriteria)
    {
        $this->gradingCriteria = $gradingCriteria;

        return $this;
    }

    /**
     * Get gradingCriteria.
     *
     * @return \Innova\CollecticielBundle\Entity\GradingCriteria
     */
    public function getGradingCriteria()
    {
        return $this->gradingCriteria;
    }

    /**
     * Set notation.
     *
     * @param \Innova\CollecticielBundle\Entity\Notation $notation
     *
     * @return ChoiceCriteria
     */
    public function setNotation(Notation $notation)
    {
        $this->notation = $notation;

        return $this;
    }

    /**
     * Get notation.
     *
     * @return \Innova\CollecticielBundle\Entity\Notation
     */
    public function getNotation()
    {
        return $this->notation;
    }

    /**
     * Set gradingNotation.
     *
     * @param \Innova\CollecticielBundle\Entity\GradingNotation $gradingNotation
     *
     * @return ChoiceNotation
     */
    public function setGradingNotation(GradingNotation $gradingNotation)
    {
        $this->gradingNotation = $gradingNotation;

        return $this;
    }

    /**
     * Get gradingNotation.
     *
     * @return \Innova\CollecticielBundle\Entity\GradingNotation
     */
    public function getGradingNotation()
    {
        return $this->gradingNotation;
    }
}
