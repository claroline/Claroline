<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Subscriber\Tool;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceTrashSubscriber implements EventSubscriberInterface
{
    const NAME = 'resource_trash';

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::WORKSPACE, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}
