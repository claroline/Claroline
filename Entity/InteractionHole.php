<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionHoleRepository")
 * @ORM\Table(name="ujm_interaction_hole")
 */
class InteractionHole extends AbstractInteraction
{
    /**
     * @ORM\Column(type="text")
     */
    private $html;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $htmlWithoutValue;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Hole",
     *     mappedBy="interactionHole",
     *     cascade={"remove"}
     * )
     */
    private $holes;

    public function __construct()
    {
        $this->holes = new ArrayCollection();
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param string $htmlWithoutValue
     */
    public function setHtmlWithoutValue($htmlWithoutValue)
    {
        $this->htmlWithoutValue = $htmlWithoutValue;
    }

    /**
     * @return string
     */
    public function getHtmlWithoutValue()
    {
        return $this->htmlWithoutValue;
    }

    /**
     * @return ArrayCollection
     */
    public function getHoles()
    {
        return $this->holes;
    }

    /**
     * @param Hole $hole
     */
    public function addHole(Hole $hole)
    {
        $this->holes->add($hole);
        $hole->setInteractionHole($this);
    }

    /**
     * @param Hole $hole
     */
    public function removeHole(Hole $hole)
    {
        $this->holes->removeElement($hole);
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->question = clone $this->question;
            $newHoles = new ArrayCollection();

            foreach ($this->holes as $hole) {
                $newHole = clone $hole;
                $newHole->setInteractionHole($this);
                $newHoles->add($newHole);
            }

            $this->holes = $newHoles;
        }
    }
}
