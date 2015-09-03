<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\InteractionQCM
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionQCMRepository")
 * @ORM\Table(name="ujm_interaction_qcm")
 */
class InteractionQCM
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

     /**
     * @var boolean $shuffle
     *
     * @ORM\Column(name="shuffle", type="boolean", nullable=true)
     */
    private $shuffle = false;

    /**
     * @var float $scoreRightResponse
     *
     * @ORM\Column(name="score_right_response", type="float", nullable=true)
     */
    private $scoreRightResponse;

    /**
     * @var float $scoreFalseResponse
     *
     * @ORM\Column(name="score_false_response", type="float", nullable=true)
     */
    private $scoreFalseResponse;

     /**
     * @var boolean $weightResponse
     *
     * @ORM\Column(name="weight_response", type="boolean", nullable=true)
     */
    private $weightResponse = false;

    /**
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Choice", mappedBy="interactionQCM", cascade={"remove"})
     */
    private $choices;

    /**
     * @ORM\OneToOne(targetEntity="UJM\ExoBundle\Entity\Interaction", cascade={"remove"})
     */
    private $interaction;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\TypeQCM")
     * @ORM\JoinColumn(name="type_qcm_id", referencedColumnName="id")
     */
    private $typeQCM;

    /**
     * Constructs a new instance of choices
     */
    public function __construct()
    {
        $this->choices = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getInteraction()
    {
        return $this->interaction;
    }

    public function setInteraction(Interaction $interaction)
    {
        $this->interaction = $interaction;
    }

    public function getTypeQCM()
    {
        return $this->typeQCM;
    }

    public function setTypeQCM(TypeQCM $typeQCM)
    {
        $this->typeQCM = $typeQCM;
    }

    /**
     * Set shuffle
     *
     * @param boolean $shuffle
     */
    public function setShuffle($shuffle)
    {
        $this->shuffle = $shuffle;
    }

    /**
     * Get shuffle
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * Set scoreRightResponse
     *
     * @param float $scoreRightResponse
     */
    public function setScoreRightResponse($scoreRightResponse)
    {
        $this->scoreRightResponse = $scoreRightResponse;
    }

    /**
     * Get scoreRightResponse
     *
     * @return float
     */
    public function getScoreRightResponse()
    {
        return $this->scoreRightResponse;
    }

    /**
     * Set scoreFalseResponse
     *
     * @param float $scoreFalseResponse
     */
    public function setScoreFalseResponse($scoreFalseResponse)
    {
        $this->scoreFalseResponse = $scoreFalseResponse;
    }

    /**
     * Get scoreFalseResponse
     *
     * @return float
     */
    public function getScoreFalseResponse()
    {
        return $this->scoreFalseResponse;
    }

    /**
     * Set weightResponse
     *
     * @param boolean $weightResponse
     */
    public function setWeightResponse($weightResponse)
    {
        $this->weightResponse = $weightResponse;
    }

    /**
     * Get weightResponse
     */
    public function getWeightResponse()
    {
        return $this->weightResponse;
    }

    public function getChoices()
    {
        return $this->choices;
    }

    public function addChoice(Choice $choice)
    {
        $this->choices[] = $choice;
        //le choix est bien lié à l'entité interactionqcm, mais dans l'entité choice il faut
        //aussi lié l'interactionqcm double travail avec les relations bidirectionnelles avec
        //lesquelles il faut bien faire attention à garder les données cohérentes dans un autre
        //script il faudra exécuter $interactionqcm->addChoice() qui garde la cohérence entre les
        //deux entités, il ne faudra pas exécuter $choice->setInteractionQCM(), car lui ne garde
        //pas la cohérence
        $choice->setInteractionQCM($this);
    }

    public function shuffleChoices()
    {
        $this->sortChoices();
        $i = 0;
        $tabShuffle = array();
        $tabFixed   = array();
        $choices = new ArrayCollection();
        $choiceCount = count($this->choices);

        while ($i < $choiceCount) {
            if ($this->choices[$i]->getPositionForce() === false) {
                $tabShuffle[$i] = $i;
                $tabFixed[] = -1;
            } else {
                $tabFixed[] = $i;
            }

            $i++;
        }
        shuffle($tabShuffle);

        $i = 0;
        $choiceCount = count($this->choices);

        while ($i < $choiceCount) {
            if ($tabFixed[$i] != -1) {
                $choices[] = $this->choices[$i];
            } else {
                $index = $tabShuffle[0];
                $choices[] = $this->choices[$index];
                unset($tabShuffle[0]);
                $tabShuffle = array_merge($tabShuffle);
            }

            $i++;
        }

        $this->choices = $choices;
    }

    public function sortChoices()
    {
        $tab = array();
        $choices = new ArrayCollection();

        foreach ($this->choices as $choice) {
            $tab[] = $choice->getOrdre();
        }

        asort($tab);

        foreach (array_keys($tab) as $indice) {
            $choices[] = $this->choices[$indice];
        }

        $this->choices = $choices;
    }

    public function __clone() {
        if ($this->id) {
            $this->id = null;

            $this->interaction = clone $this->interaction;

            $newChoices = new ArrayCollection();
            foreach ($this->choices as $choice) {
                $newChoice = clone $choice;
                $newChoice->setInteractionQCM($this);
                $newChoices->add($newChoice);
            }
            $this->choices = $newChoices;
        }
    }
}
