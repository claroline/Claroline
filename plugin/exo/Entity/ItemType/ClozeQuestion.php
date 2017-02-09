<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Misc\Hole;

/**
 * A Cloze question.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_interaction_hole")
 */
class ClozeQuestion extends AbstractItem
{
    /**
     * The HTML text with empty holes.
     *
     * @ORM\Column(name="htmlWithoutValue", type="text")
     *
     * @var string
     */
    private $text;

    /**
     * The list of holes present in the text.
     *
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\Hole",
     *     mappedBy="interactionHole",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    private $holes;

    /**
     * ClozeQuestion constructor.
     */
    public function __construct()
    {
        $this->holes = new ArrayCollection();
    }

    /**
     * Gets text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets text.
     *
     * @param $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Gets holes.
     *
     * @return ArrayCollection
     */
    public function getHoles()
    {
        return $this->holes;
    }

    /**
     * Retrieves a hole by its uuid.
     *
     * @param $uuid
     *
     * @return Hole
     */
    public function getHole($uuid)
    {
        $found = null;
        foreach ($this->holes as $hole) {
            if ($hole->getUuid() === $uuid) {
                $found = $hole;
                break;
            }
        }

        return $found;
    }

    /**
     * Adds a hole.
     *
     * @param Hole $hole
     */
    public function addHole(Hole $hole)
    {
        if (!$this->holes->contains($hole)) {
            $this->holes->add($hole);
            $hole->setInteractionHole($this);
        }
    }

    /**
     * Removes a hole.
     *
     * @param Hole $hole
     */
    public function removeHole(Hole $hole)
    {
        if ($this->holes->contains($hole)) {
            $this->holes->removeElement($hole);
        }
    }
}
