<?php

namespace HeVinci\UrlBundle\Listener;

use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use HeVinci\UrlBundle\Entity\Url;
use HeVinci\UrlBundle\Serializer\UrlSerializer;

class UrlListener
{
    /** @var UrlSerializer */
    private $serializer;

    /**
     * UrlListener constructor.
     *
     * @param UrlSerializer $serializer
     */
    public function __construct(UrlSerializer $serializer)
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
        /** @var Url $url */
        $url = $event->getResource();

        $event->setData([
            'url' => $this->serializer->serialize($url),
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
