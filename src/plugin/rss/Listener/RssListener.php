<?php

namespace Claroline\RssBundle\Listener;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;

class RssListener
{
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Loads an URL resource.
     */
    public function onLoad(LoadResourceEvent $event)
    {
        $rss = $event->getResource();

        $event->setData([
            'rssFeed' => $this->serializer->serialize($rss),
        ]);

        $event->stopPropagation();
    }
}
