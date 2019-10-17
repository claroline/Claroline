<?php

namespace Icap\DropzoneBundle;

use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeleteListener
{
    private $container;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $resource = $event->getResource();

        foreach ($resource->getDrops() as $drop) {
            $em->remove($drop);
        }
        $em->remove($event->getResource());
        $event->stopPropagation();
    }
}
