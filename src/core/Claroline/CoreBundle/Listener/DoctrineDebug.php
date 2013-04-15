<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\Event\OnFlushEventArgs;

class DoctrineDebug extends ContainerAware
{
    /**
     * Gets all the entities to flush
     *
     * @param OnFlushEventArgs $eventArgs Event args
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        print_r('flush !');
    }
}
