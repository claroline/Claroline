<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Listener\Tool;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\DisplayToolEvent;

class AgendaListener
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $event->setContent([]);
        $event->stopPropagation();
    }

    /**
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
