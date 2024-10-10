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
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: WidgetInstance::class, cascade: ['persist'])]
    private ?WidgetInstance $widgetInstance = null;

    public function getWidgetInstance(): ?WidgetInstance
    {
        return $this->widgetInstance;
    }

    public function setWidgetInstance(WidgetInstance $widgetInstance): void
    {
        $this->widgetInstance = $widgetInstance;
    }
}
