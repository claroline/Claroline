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
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when a widget is configured.
 */
class ConfigureWidgetEvent extends Event implements DataConveyorEventInterface
{
    private $isPopulated = false;
    private $instance;
    private $admin;

    /**
     * Constructor.
     *
     * @param Workspace $workspace
     */
    public function __construct(WidgetInstance $instance, $admin = false)
    {
        $this->instance = $instance;
        $this->admin = $admin;
    }

    public function setContent($content)
    {
        $this->isPopulated = true;
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }

    public function getInstance()
    {
        return $this->instance;
    }

    public function isAdmin()
    {
        return $this->admin;
    }
}
