<?php

namespace Claroline\RssBundle\Listener;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;

class RssListener
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Loads an URL resource.
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        $rss = $event->getResource();

        $event->setData([
            'rssFeed' => $this->serializer->serialize($rss),
        ]);

        $event->stopPropagation();
    }

    /**
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }
}
