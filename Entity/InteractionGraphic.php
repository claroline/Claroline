<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\InteractionGraphic
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\InteractionGraphicRepository")
 * @ORM\Table(name="ujm_interaction_graphic")
 */
class InteractionGraphic
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
     * @var integer $width
     *
     * @ORM\Column(name="width", type="integer")
     */
    private $width;

    /**
     * @var integer $height
     *
     * @ORM\Column(name="height", type="integer")
     */
    private $height;

     /**
     * @ORM\OneToOne(targetEntity="UJM\ExoBundle\Entity\Interaction", cascade={"remove"})
     */
    private $interaction;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Document")
     */
    private $document;

    /**
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Coords", mappedBy="interactionGraphic", cascade={"remove"})
     */
    private $coords;

    /**
     * Constructs a new instance of choices
     */
    public function __construct()
    {
        $this->coords = new \Doctrine\Common\Collections\ArrayCollection;
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
     * Set width
     *
     * @param integer $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    public function getInteraction()
    {
        return $this->interaction;
    }

    public function setInteraction(\UJM\ExoBundle\Entity\Interaction $interaction)
    {
        $this->interaction = $interaction;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setDocument(\UJM\ExoBundle\Entity\Document $document)
    {
        $this->document = $document;
    }

    public function getCoords()
    {
        return $this->coords;
    }

    public function addCoord(\UJM\ExoBundle\Entity\Coords $coord)
    {
        $this->coords[] = $coord;
        //le choix est bien lié à l'entité interactionqcm, mais dans l'entité choice il faut
        //aussi lié l'interactionqcm double travail avec les relations bidirectionnelles avec
        //lesquelles il faut bien faire attention à garder les données cohérentes dans un autre
        //script il faudra exécuter $interactionqcm->addChoice() qui garde la cohérence entre les
        //deux entités, il ne faudra pas exécuter $choice->setInteractionQCM(), car lui ne garde
        //pas la cohérence
        $coord->setInteractionGraphic($this);
    }

    public function __clone() {
        if ($this->id) {
            $this->id = null;

            $this->interaction = clone $this->interaction;

            $newCoords = new \Doctrine\Common\Collections\ArrayCollection;
            foreach ($this->coords as $coord) {
                $newCoord = clone $coord;
                $newCoord->setInteractionGraphic($this);
                $newCoords->add($newCoord);
            }
            $this->coords = $newCoords;
        }
    }
}
