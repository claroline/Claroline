<?php

namespace Claroline\AnalyticsBundle\Entity\Widget\Type;

use Claroline\CoreBundle\Entity\Widget\Type\AbstractWidget;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProgressionWidget.
 *
 * Permits to display the list of resources of a workspace and whether the user has opened it yet.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_widget_progression")
 */
class ProgressionWidget extends AbstractWidget
{
    /**
     * The HTML content of the widget.
     *
     * @ORM\Column(name="level_max", type="integer", nullable=true)
     *
     * @var int
     */
    private $levelMax = 1;

    /**
     * Get level max.
     *
     * @return int
     */
    public function getLevelMax()
    {
        return $this->levelMax;
    }

    /**
     * Set level max.
     *
     * @param int $levelMax
     */
    public function setLevelMax($levelMax)
    {
        $this->levelMax = $levelMax;
    }
}
