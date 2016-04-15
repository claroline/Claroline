<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Label.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\LabelRepository")
 * @ORM\Table(name="ujm_label")
 */
class Label
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text")
     */
    private $value;

    /**
     * @var float
     *
     * @ORM\Column(name="score_right_response", type="float", nullable=true)
     */
    private $scoreRightResponse;

    /**
     * @var bool
     *
     * @ORM\Column(name="position_force", type="boolean", nullable=true)
     */
    private $positionForce;

    /**
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\InteractionMatching", inversedBy="labels")
     * @ORM\JoinColumn(name="interaction_matching_id", referencedColumnName="id")
     */
    private $interactionMatching;

    /**
     * @var text
     *
     * @ORM\Column(name="feedback", type="text", nullable=true)
     */
    private $feedback;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     */
    private $resourceNode;

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
     * Set value.
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set scoreRightResponse.
     *
     * @param float $scoreRightResponse
     */
    public function setScoreRightResponse($scoreRightResponse)
    {
        $this->scoreRightResponse = $scoreRightResponse;
    }

    /**
     * Get scoreRightResponse.
     *
     * @return float
     */
    public function getScoreRightResponse()
    {
        return $this->scoreRightResponse;
    }

    /**
     * Get InteractionMatching.
     *
     * @return InteractionMatching
     */
    public function getInteractionMatching()
    {
        return $this->interactionMatching;
    }

    /**
     * Set InteractionMatching.
     */
    public function setInteractionMatching(\UJM\ExoBundle\Entity\InteractionMatching $interactionMatching)
    {
        $this->interactionMatching = $interactionMatching;
    }

    /**
     * Set positionForce.
     *
     * @param bool $positionForce
     */
    public function setPositionForce($positionForce)
    {
        $this->positionForce = $positionForce;
    }

    /**
     * Get positionForce.
     */
    public function getPositionForce()
    {
        return $this->positionForce;
    }

    /**
     * Set ordre.
     *
     * @param int $ordre
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;
    }

    /**
     * Get ordre.
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }
    /**
     * get feedback.
     *
     * @return string
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * set feedback.
     *
     * @param \UJM\ExoBundle\Entity\text $feedback
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * @param ResourceNode $resourceNode
     */
    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }
}
