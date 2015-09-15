<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionOpenRepository")
 * @ORM\Table(name="ujm_interaction_open")
 */
class InteractionOpen extends AbstractInteraction
{
    /**
     * @ORM\Column(name="orthography_correct", type="boolean")
     */
    private $orthographyCorrect = false;

    /**
     * @ORM\ManyToOne(targetEntity="TypeOpenQuestion")
     */
    private $typeopenquestion;

    /**
     * @ORM\OneToMany(
     *     targetEntity="WordResponse",
     *     mappedBy="interactionopen",
     *     cascade={"remove"}
     * )
     */
    private $wordResponses;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $scoreMaxLongResp;

    public function __construct()
    {
        $this->wordResponses = new ArrayCollection();
    }

    /**
     * @param boolean $orthographyCorrect
     */
    public function setOrthographyCorrect($orthographyCorrect)
    {
        $this->orthographyCorrect = $orthographyCorrect;
    }

    /**
     * Get orthographyCorrect.
     */
    public function getOrthographyCorrect()
    {
        return $this->orthographyCorrect;
    }

    /**
     * @return TypeOpenQuestion
     */
    public function getTypeOpenQuestion()
    {
        return $this->typeopenquestion;
    }

    /**
     * @param TypeOpenQuestion $typeOpenQuestion
     */
    public function setTypeOpenQuestion(TypeOpenQuestion $typeOpenQuestion)
    {
        $this->typeopenquestion = $typeOpenQuestion;
    }

    /**
     * @return ArrayCollection
     */
    public function getWordResponses()
    {
        return $this->wordResponses;
    }

    /**
     * @param WordResponse $wordResponse
     */
    public function addWordResponse(WordResponse $wordResponse)
    {
        $this->wordResponses->add($wordResponse);
        $wordResponse->setInteractionOpen($this);
    }

    /**
     * @param WordResponse $wordResponse
     */
    public function removeWordResponse(WordResponse $wordResponse)
    {
        $this->wordResponses->removeElement($wordResponse);
    }

    /**
     * @param float $scoreMaxLongResp
     */
    public function setScoreMaxLongResp($scoreMaxLongResp)
    {
        $this->scoreMaxLongResp = $scoreMaxLongResp;
    }

    /**
     * @return float
     */
    public function getScoreMaxLongResp()
    {
        return $this->scoreMaxLongResp;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->question= clone $this->question;
            $newWordResponses = new ArrayCollection();

            foreach ($this->wordResponses as $wordResponse) {
                $newWordResponse = clone $wordResponse;
                $newWordResponse->setInteractionOpen($this);
                $newWordResponses->add($newWordResponse);
            }

            $this->wordResponses = $newWordResponses;
        }
    }
}
