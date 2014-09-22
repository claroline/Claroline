<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Symfony\Component\EventDispatcher\Event;

class CopyWidgetConfigurationEvent extends Event implements DataConveyorEventInterface
{
    private $isPopulated = false;
    private $widgetInstance;
    private $widgetInstanceCopy;

    public function __construct(
        WidgetInstance $widgetInstance,
        WidgetInstance $widgetInstanceCopy
    )
    {
        $this->widgetInstanceCopy = $widgetInstanceCopy;
        $this->widgetInstance = $widgetInstance;
    }

    public function validateCopy()
    {
        $this->isPopulated = true;
    }

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    public function getWidgetInstanceCopy()
    {
        return $this->widgetInstanceCopy;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
