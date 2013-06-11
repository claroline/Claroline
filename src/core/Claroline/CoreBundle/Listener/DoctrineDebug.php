<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\Event\OnFlushEventArgs;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\DiExtraBundle\Annotation\Tag as Tag;

/**
 * @DI\Service
 * @Tag("doctrine.event_listener", attributes={"event"="onFlush"})
 */
class DoctrineDebug extends ContainerAware
{
    /**
     * @DI\Observe("onFlush")
     *
     * @param WorkspaceLogEvent $event
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        print_r(PHP_EOL.'flush !');
    }
}
