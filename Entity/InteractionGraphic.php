<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ujm_interaction_graphic")
 */
class InteractionGraphic extends AbstractInteraction
{
    const TYPE = 'InteractionGraphic';

    /**
     * @ORM\ManyToOne(targetEntity="Picture")
     */
    private $picture;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Coords",
     *     mappedBy="interactionGraphic",
     *     cascade={"remove"}
     * )
     */
    private $coords;

    /**
     * Constructs a new instance of choices.
     */
    public function __construct()
    {
        $this->coords = new ArrayCollection();
    }

    /**
     * @return string
     */
    public static function getQuestionType()
    {
        return self::TYPE;
    }

    /**
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return Picture
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param Picture $picture
     */
    public function setPicture(Picture $picture)
    {
        $this->picture = $picture;
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
