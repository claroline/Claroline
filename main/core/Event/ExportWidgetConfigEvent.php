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

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class ExportWidgetConfigEvent extends Event implements DataConveyorEventInterface
{
    private $config;
    private $workspace;
    private $widget;
    private $isPopulated = false;

    public function __construct(Widget $widget, Workspace $workspace)
    {
        $this->widget = $widget;
        $this->workspace = $workspace;
    }

    public function setConfig(array $config)
    {
        $this->isPopulated = true;
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getWidget()
    {
        return $this->widget;
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
