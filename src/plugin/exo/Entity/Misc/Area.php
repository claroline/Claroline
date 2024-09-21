<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\GraphicQuestion;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * Area.
 */
#[ORM\Table(name: 'ujm_coords')]
#[ORM\Entity]
class Area implements AnswerPartInterface
{
    use Id;
    use FeedbackTrait;
    use ScoreTrait;
    use Uuid;

    /**
     * @var string
     */
    #[ORM\Column]
    private $value;

    /**
     * @var string
     */
    #[ORM\Column]
    private $shape;

    /**
     * @var string
     */
    #[ORM\Column]
    private $color = '000000';

    /**
     * @var float
     */
    #[ORM\Column(type: Types::FLOAT)]
    private $size;

    /**
     * @deprecated this needs to be deleted to keep things separated
     */
    #[ORM\JoinColumn(name: 'interaction_graphic_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: GraphicQuestion::class, inversedBy: 'areas')]
    private $interactionGraphic;

    public function __construct()
    {
        $this->refreshUuid();
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

    public function setInteractionGraphic(GraphicQuestion $interactionGraphic)
    {
        $this->interactionGraphic = $interactionGraphic;
    }
}
