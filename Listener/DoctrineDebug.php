<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\Event\OnFlushEventArgs;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.doctrine.debug")
 * @DI\Tag("doctrine.event_listener", attributes={"event"="onFlush"})
 */
class DoctrineDebug extends ContainerAware
{
    /**
     * Gets all the entities to flush
     *
     * @param OnFlushEventArgs $eventArgs Event args
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        //uncomment this line for doctrine insert optimization !
        //reduce the amount of flushes to increase performances
//        echo('flush !');
    }
}
