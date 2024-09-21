<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Content\Image;
use UJM\ExoBundle\Entity\Misc\Area;

/**
 * A Graphic question.
 */
#[ORM\Table(name: 'ujm_interaction_graphic')]
#[ORM\Entity]
class GraphicQuestion extends AbstractItem
{
    const SHAPE_RECT = 'rect';
    const SHAPE_CIRCLE = 'circle';

    /**
     * The image of the question.
     *
     *
     * @var Image
     */
    #[ORM\ManyToOne(targetEntity: Image::class, cascade: ['persist'])]
    private ?Image $image = null;

    /**
     * @todo remove the mapped by and add a join table
     * @var Collection<int, Area>
     */
    #[ORM\OneToMany(targetEntity: Area::class, mappedBy: 'interactionGraphic', cascade: ['all'], orphanRemoval: true)]
    private Collection $areas;

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
     */
    public function removeArea(Area $area)
    {
        if ($this->areas->contains($area)) {
            $this->areas->removeElement($area);
        }
    }
}
