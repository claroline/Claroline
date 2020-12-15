<?php

namespace UJM\ExoBundle\Entity\Misc;

use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * ColorSelection.
 *
 * @ORM\Entity()
 * @ORM\Table(name="ujm_color_selection")
 */
class ColorSelection implements AnswerPartInterface
{
    use ScoreTrait;

    use FeedbackTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Selection", inversedBy="colorSelections")
     * @ORM\JoinColumn(name="selection_id", referencedColumnName="id")
     */
    private $selection;

    /**
     * @ORM\ManyToOne(targetEntity="Color", inversedBy="colorSelections")
     * @ORM\JoinColumn(name="color_id", referencedColumnName="id")
     */
    private $color;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Selection
     */
    public function getSelection()
    {
        return $this->selection;
    }

    /**
     * @param Selection $selection
     */
    public function setSelection(Selection $selection)
    {
        $this->selection = $selection;
    }

    public function setColor(Color $color)
    {
        $this->color = $color;
    }

    public function getColor()
    {
        return $this->color;
    }
}
