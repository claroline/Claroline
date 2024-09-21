<?php

namespace UJM\ExoBundle\Entity\Misc;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Model\FeedbackTrait;
use UJM\ExoBundle\Library\Model\ScoreTrait;

/**
 * ColorSelection.
 */
#[ORM\Table(name: 'ujm_color_selection')]
#[ORM\Entity]
class ColorSelection implements AnswerPartInterface
{
    use Id;
    use ScoreTrait;
    use FeedbackTrait;

    #[ORM\JoinColumn(name: 'selection_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Selection::class, inversedBy: 'colorSelections')]
    private $selection;

    #[ORM\JoinColumn(name: 'color_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Color::class, inversedBy: 'colorSelections')]
    private $color;

    /**
     * @return Selection
     */
    public function getSelection()
    {
        return $this->selection;
    }

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
