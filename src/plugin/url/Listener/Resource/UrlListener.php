<?php

namespace HeVinci\UrlBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use HeVinci\UrlBundle\Entity\Url;

class UrlListener
{
    public function __construct(
        private readonly SerializerProvider $serializer
    ) {
    }

    /**
     * Loads a URL resource.
     */
    public function onLoad(LoadResourceEvent $event): void
    {
        /** @var Url $url */
        $url = $event->getResource();

        $event->setData([
            'url' => $this->serializer->serialize($url),
        ]);

        $event->stopPropagation();
    }
}
