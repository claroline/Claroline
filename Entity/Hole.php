<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Hole.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_hole")
 */
class Hole
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
     * @var int
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var bool
     *
     * @ORM\Column(name="orthography", type="boolean", nullable=true)
     */
    private $orthography;

    /**
     * @var bool
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
     * Constructs a new instance of choices.
     */
    public function __construct()
    {
        $this->wordResponses = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set size.
     *
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set position.
     *
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set orthography.
     *
     * @param int $orthography
     */
    public function setOrthography($orthography)
    {
        $this->orthography = $orthography;
    }

    /**
     * Get orthography.
     */
    public function getOrthography()
    {
        return $this->orthography;
    }

    /**
     * Set selector.
     *
     * @param int $selector
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
    }

    /**
     * Get selector.
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

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;

            $newWordResponses = new \Doctrine\Common\Collections\ArrayCollection();
            foreach ($this->wordResponses as $wordResponse) {
                $newWordResponse = clone $wordResponse;
                $newWordResponse->setHole($this);
                $newWordResponses->add($newWordResponse);
            }
            $this->wordResponses = $newWordResponses;
        }
    }
}
