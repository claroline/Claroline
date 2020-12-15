<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 */
abstract class AbstractWidget
{
    use Id;

    /**
     * The parent instance.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var WidgetInstance
     */
    private $widgetInstance;

    /**
     * Get widget instance.
     *
     * @return WidgetInstance
     */
    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    /**
     * Set widget instance.
     *
     * @param WidgetInstance $widgetInstance
     */
    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;
    }
}
