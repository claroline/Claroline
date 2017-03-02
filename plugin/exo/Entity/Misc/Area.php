<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use UJM\ExoBundle\Entity\ItemType\GraphicQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;
use UJM\ExoBundle\Library\Model\UuidTrait;

/**
 * Area.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_coords")
 */
class Area implements AnswerPartInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    use UuidTrait;

    use ScoreTrait;

    use FeedbackTrait;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $shape;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $color;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $size;

    /**
     * @deprecated this needs to be deleted to keep things separated
     *
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\ItemType\GraphicQuestion", inversedBy="areas")
     * @ORM\JoinColumn(name="interaction_graphic_id", referencedColumnName="id")
     */
    private $interactionGraphic;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $shape
     */
    public function setShape($shape)
    {
        $this->shape = $shape;
    }

    /**
     * @return string
     */
    public function getShape()
    {
        return $this->shape;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param float $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return GraphicQuestion
     */
    public function getInteractionGraphic()
    {
        return $this->interactionGraphic;
    }

    /**
     * @param GraphicQuestion $interactionGraphic
     */
    public function setInteractionGraphic(GraphicQuestion $interactionGraphic)
    {
        $this->interactionGraphic = $interactionGraphic;
    }
}
