<?php

namespace Claroline\CoreBundle\Entity\Badge\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_widget_badge_usage_config")
 */
class BadgeUsageConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Claroline\CoreBundle\Entity\Widget\WidgetInstance
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $widgetInstance;

    /**
     * @var integer
     * @ORM\Column(type="smallint")
     */
    protected $numberAwardedBadge;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $widgetInstance
     *
     * @return BadgeUsageConfig
     */
    public function setWidgetInstance($widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Widget\WidgetInstance
     */
    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    /**
     * @param mixed $numberAwardedBadge
     *
     * @return BadgeUsageConfig
     */
    public function setNumberAwardedBadge($numberAwardedBadge)
    {
        $this->numberAwardedBadge = $numberAwardedBadge;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumberAwardedBadge()
    {
        return $this->numberAwardedBadge;
    }
}
 