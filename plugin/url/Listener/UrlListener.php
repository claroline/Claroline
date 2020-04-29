<?php

namespace HeVinci\UrlBundle\Listener;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use HeVinci\UrlBundle\Entity\Url;
use HeVinci\UrlBundle\Manager\UrlManager;

class UrlListener
{
    /** @var SerializerProvider */
    private $serializer;
    /** @var UrlManager */
    private $manager;

    /**
     * UrlListener constructor.
     *
     * @param SerializerProvider $serializer
     * @param UrlManager         $manager
     */
    public function __construct(
        SerializerProvider $serializer,
        UrlManager $manager
    ) {
        $this->serializer = $serializer;
        $this->manager = $manager;
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
            'placeholders' => $this->manager->getAvailablePlaceholders(),
        ]);

        $event->stopPropagation();
    }
}
