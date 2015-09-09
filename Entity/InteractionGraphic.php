<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionGraphicRepository")
 * @ORM\Table(name="ujm_interaction_graphic")
 */
class InteractionGraphic extends AbstractInteraction
{
    /**
     * @ORM\Column(type="integer")
     */
    private $width;

    /**
     * @ORM\Column(type="integer")
     */
    private $height;

    /**
     * @ORM\ManyToOne(targetEntity="Document")
     */
    private $document;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Coords",
     *     mappedBy="interactionGraphic",
     *     cascade={"remove"}
     * )
     */
    private $coords;

    /**
     * Constructs a new instance of choices
     */
    public function __construct()
    {
        $this->coords = new ArrayCollection();
    }

    /**
     * @param integer $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param integer $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param Question $question
     */
    public function setQuestion(Question $question)
    {
        $this->question = $question;
    }

    /**
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param Document $document
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;
    }

    /**
     * @return ArrayCollection
     */
    public function getCoords()
    {
        return $this->coords;
    }

    /**
     * @param Coords $coord
     */
    public function addCoord(Coords $coord)
    {
        $this->coords->add($coord);
        $coord->setInteractionGraphic($this);
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->question = clone $this->question;
            $newCoords = new ArrayCollection;

            foreach ($this->coords as $coord) {
                $newCoord = clone $coord;
                $newCoord->setInteractionGraphic($this);
                $newCoords->add($newCoord);
            }

            $this->coords = $newCoords;
        }
    }
}
