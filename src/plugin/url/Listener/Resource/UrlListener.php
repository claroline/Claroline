<?php

namespace HeVinci\UrlBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use HeVinci\UrlBundle\Entity\Url;

class UrlListener
{
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        SerializerProvider $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Loads an URL resource.
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Url $url */
        $url = $event->getResource();

        $event->setData([
            'url' => $this->serializer->serialize($url),
        ]);

        $event->stopPropagation();
    }
}
