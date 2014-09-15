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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Symfony\Component\EventDispatcher\Event;

class CopyWidgetConfigurationEvent extends Event implements DataConveyorEventInterface
{
    private $isPopulated = false;
    private $widgetInstance;
    private $widgetInstanceCopy;
    private $workspace;

    public function __construct(
        WidgetInstance $widgetInstance,
        WidgetInstance $widgetInstanceCopy,
        Workspace $workspace
    )
    {
        $this->widgetInstanceCopy = $widgetInstanceCopy;
        $this->widgetInstance = $widgetInstance;
        $this->workspace = $workspace;
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

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
