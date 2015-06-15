<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\InteractionHole
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionHoleRepository")
 * @ORM\Table(name="ujm_interaction_hole")
 */
class InteractionHole
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
     * @var text $html
     *
     * @ORM\Column(name="html", type="text")
     */
    private $html;

    /**
     * @var text $htmlWithoutValue
     *
     * @ORM\Column(name="htmlWithoutValue", type="text", nullable=true)
     */
    private $htmlWithoutValue;

    /**
     * @ORM\OneToOne(targetEntity="UJM\ExoBundle\Entity\Interaction", cascade={"remove"})
     */
    private $interaction;

    /**
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Hole", mappedBy="interactionHole", cascade={"remove"})
     */
    private $holes;

    public function __construct()
    {
        $this->holes = new \Doctrine\Common\Collections\ArrayCollection;
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
     * Set html
     *
     * @param text $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * Get html
     *
     * @return text
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set htmlWithoutValue
     *
     * @param text $htmlWithoutValue
     */
    public function setHtmlWithoutValue($htmlWithoutValue)
    {
        $this->htmlWithoutValue = $htmlWithoutValue;
    }

    /**
     * Get htmlWithoutValue
     *
     * @return text
     */
    public function getHtmlWithoutValue()
    {
        return $this->htmlWithoutValue;
    }

    public function getInteraction()
    {
        return $this->interaction;
    }

    public function setInteraction(\UJM\ExoBundle\Entity\Interaction $interaction)
    {
        $this->interaction = $interaction;
    }

    public function getHoles()
    {

        return $this->holes;
    }

    public function addHole(\UJM\ExoBundle\Entity\Hole $hole)
    {
        $this->holes[] = $hole;

        $hole->setInteractionHole($this);
    }

    public function removeHole(\UJM\ExoBundle\Entity\Hole $hole)
    {

    }

    public function __clone() {
        if ($this->id) {
            $this->id = null;

            $this->interaction = clone $this->interaction;

            $newHoles = new \Doctrine\Common\Collections\ArrayCollection;
            foreach ($this->holes as $hole) {
                $newHole = clone $hole;
                $newHole->setInteractionHole($this);
                $newHoles->add($newHole);
            }
            $this->holes = $newHoles;

        }
    }

}
