<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Content\Image;
use UJM\ExoBundle\Entity\Misc\Area;

/**
 * A Graphic question.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_interaction_graphic")
 */
class GraphicQuestion extends AbstractItem
{
    const SHAPE_RECT = 'rect';
    const SHAPE_CIRCLE = 'circle';

    /**
     * The image of the question.
     *
     * @ORM\ManyToOne(
     *     targetEntity="UJM\ExoBundle\Entity\Content\Image",
     *     cascade={"persist"}
     * )
     *
     * @var Image
     */
    private $image;

    /**
     * @todo remove the mapped by and add a join table
     *
     * @ORM\OneToMany(
     *     targetEntity="UJM\ExoBundle\Entity\Misc\Area",
     *     mappedBy="interactionGraphic",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $areas;

    /**
     * GraphicQuestion constructor.
     */
    public function __construct()
    {
        $this->areas = new ArrayCollection();
    }

    /**
     * Gets image.
     *
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Sets image.
     *
     * @param Image $image
     */
    public function setImage(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Gets areas.
     *
     * @return ArrayCollection
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * Adds an area.
     *
     * @param Area $area
     */
    public function addArea(Area $area)
    {
        if (!$this->areas->contains($area)) {
            $this->areas->add($area);
            $area->setInteractionGraphic($this);
        }
    }

    /**
     * Removes an area.
     *
     * @param Area $area
     */
    public function removeArea(Area $area)
    {
        if ($this->areas->contains($area)) {
            $this->areas->removeElement($area);
        }
    }
}
