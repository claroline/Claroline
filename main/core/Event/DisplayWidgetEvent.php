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
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

class DisplayWidgetEvent extends Event implements DataConveyorEventInterface
{
    protected $instance;
    protected $isPopulated = false;

    public function __construct(WidgetInstance $instance)
    {
        $this->instance = $instance;
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

    public function getInstance()
    {
        return $this->instance;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
