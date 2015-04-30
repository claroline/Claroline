<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\InteractionOpen
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionOpenRepository")
 * @ORM\Table(name="ujm_interaction_open")
 */
class InteractionOpen
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
     * @var boolean $orthographyCorrect
     *
     * @ORM\Column(name="orthography_correct", type="boolean")
     */
    private $orthographyCorrect;

    /**
     * @ORM\OneToOne(targetEntity="UJM\ExoBundle\Entity\Interaction", cascade={"remove"})
     */
    private $interaction;


    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\TypeOpenQuestion")
     */
    private $typeopenquestion;

    /**
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\WordResponse", mappedBy="interactionopen", cascade={"remove"})
     */
    private $wordResponses;

    /**
     * @var float $scoreMaxLongResp
     *
     * @ORM\Column(name="scoreMaxLongResp", type="float", nullable=true)
     */
    private $scoreMaxLongResp;

    public function __construct()
    {
        $this->wordResponses = new \Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * Set orthographyCorrect
     *
     * @param boolean $orthographyCorrect
     */
    public function setOrthographyCorrect($orthographyCorrect)
    {
        $this->orthographyCorrect = $orthographyCorrect;
    }

    /**
     * Get orthographyCorrect
     */
    public function getOrthographyCorrect()
    {
        return $this->orthographyCorrect;
    }

    public function getInteraction()
    {
        return $this->interaction;
    }

    public function setInteraction(\UJM\ExoBundle\Entity\Interaction $interaction)
    {
        $this->interaction = $interaction;
    }

    public function getTypeOpenQuestion()
    {
        return $this->typeopenquestion;
    }

    public function setTypeOpenQuestion(\UJM\ExoBundle\Entity\TypeOpenQuestion $typeOpenQuestion)
    {
        $this->typeopenquestion = $typeOpenQuestion;
    }

    public function getWordResponses()
    {

        return $this->wordResponses;
    }

    public function addWordResponse(\UJM\ExoBundle\Entity\WordResponse $wordResponse)
    {
        $this->wordResponses[] = $wordResponse;

        $wordResponse->setInteractionOpen($this);
    }

    public function removeWordResponse(\UJM\ExoBundle\Entity\WordResponse $wordResponse)
    {

    }

    /**
     * Set scoreMaxLongResp
     *
     * @param float $scoreMaxLongResp
     */
    public function setScoreMaxLongResp($scoreMaxLongResp)
    {
        $this->scoreMaxLongResp = $scoreMaxLongResp;
    }

    /**
     * Get scoreMaxLongResp
     */
    public function getScoreMaxLongResp()
    {
        return $this->scoreMaxLongResp;
    }

    public function __clone() {
        if ($this->id) {
            $this->id = null;

            $this->interaction = clone $this->interaction;

            $newWordResponses = new \Doctrine\Common\Collections\ArrayCollection;
            foreach ($this->wordResponses as $wordResponse) {
                $newWordResponse = clone $wordResponse;
                $newWordResponse->setInteractionOpen($this);
                $newWordResponses->add($newWordResponse);
            }
            $this->wordResponses = $newWordResponses;

        }
    }
}
