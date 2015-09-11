<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Coords.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_coords")
 */
class Coords
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
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="shape", type="string", length=255)
     */
    private $shape;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=255)
     */
    private $color;

    /**
     * @var float
     *
     * @ORM\Column(name="score_coords", type="float")
     */
    private $scoreCoords;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\InteractionGraphic", inversedBy="coords")
     * @ORM\JoinColumn(name="interaction_graphic_id", referencedColumnName="id")
     */
    private $interactionGraphic;

    /**
     * @var float
     *
     * @ORM\Column(name="size", type="float")
     */
    private $size;

    /**
     * @var text
     *
     * @ORM\Column(name="feedback", type="text", nullable=true)
     */
    private $feedback;

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
     * Set shape.
     *
     * @param string $shape
     */
    public function setShape($shape)
    {
        $this->shape = $shape;
    }

    /**
     * Get shape.
     *
     * @return string
     */
    public function getShape()
    {
        return $this->shape;
    }

    /**
     * Set color.
     *
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * Get color.
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set scoreCoords.
     *
     * @param float $scoreCoords
     */
    public function setScoreCoords($scoreCoords)
    {
        $this->scoreCoords = $scoreCoords;
    }

    /**
     * Get scoreCoords.
     *
     * @return float
     */
    public function getScoreCoords()
    {
        return $this->scoreCoords;
    }

    /**
     * Set sizes.
     *
     * @param float $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Get size.
     *
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    public function getInteractionGraphic()
    {
        return $this->interactionGraphic;
    }

    public function setInteractionGraphic(\UJM\ExoBundle\Entity\InteractionGraphic $interactionGraphic)
    {
        $this->interactionGraphic = $interactionGraphic;
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
}
