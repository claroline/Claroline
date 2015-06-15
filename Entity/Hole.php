<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Hole
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_hole")
 */
class Hole
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
     * @var integer $size
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var boolean $orthography
     *
     * @ORM\Column(name="orthography", type="boolean", nullable=true)
     */
    private $orthography;

    /**
     * @var boolean $selector
     *
     * @ORM\Column(name="selector", type="boolean", nullable=true)
     */
    private $selector;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\InteractionHole", inversedBy="holes")
     * @ORM\JoinColumn(name="interaction_hole_id", referencedColumnName="id")
     */
    private $interactionHole;

    /**
    * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\WordResponse", mappedBy="hole", cascade={"remove"})
    */
    private $wordResponses;

    /**
    * Constructs a new instance of choices
    */
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
     * Set size
     *
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set position
     *
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set orthography
     *
     * @param integer $orthography
     */
    public function setOrthography($orthography)
    {
        $this->orthography = $orthography;
    }

    /**
     * Get orthography
     */
    public function getOrthography()
    {
        return $this->orthography;
    }

    /**
     * Set selector
     *
     * @param integer $selector
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
    }

    /**
     * Get selector
     */
    public function getSelector()
    {
        return $this->selector;
    }

    public function getInteractionHole()
    {
        return $this->interactionHole;
    }

    public function setInteractionHole(\UJM\ExoBundle\Entity\InteractionHole $interactionHole)
    {
        $this->interactionHole = $interactionHole;
    }

    public function getWordResponses()
    {
        return $this->wordResponses;
    }

    public function addWordResponse(\UJM\ExoBundle\Entity\WordResponse $wordResponse)
    {
        $this->wordResponses[] = $wordResponse;

        $wordResponse->setHole($this);
    }

    public function removeWordResponse(\UJM\ExoBundle\Entity\WordResponse $wordResponse)
    {

    }

    public function __clone() {
        if ($this->id) {
            $this->id = null;

            $newWordResponses = new \Doctrine\Common\Collections\ArrayCollection;
            foreach ($this->wordResponses as $wordResponse) {
                $newWordResponse = clone $wordResponse;
                $newWordResponse->setHole($this);
                $newWordResponses->add($newWordResponse);
            }
            $this->wordResponses = $newWordResponses;

        }
    }
}
