<?php

namespace Claroline\CoreBundle\Entity\Widget\Type;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractWidget
{
    use Id;

    /**
     * The parent instance.
     *
     *
     * @var WidgetInstance
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: WidgetInstance::class, cascade: ['persist'])]
    private ?WidgetInstance $widgetInstance = null;

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
     */
    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;
    }
}
