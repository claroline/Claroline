<?php

namespace HeVinci\UrlBundle\Listener;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;
use HeVinci\UrlBundle\Entity\Url;

class UrlListener
{
    /** @var SerializerProvider */
    private $serializer;
    /** @var PlaceholderManager */
    private $placeholderManager;

    /**
     * UrlListener constructor.
     *
     * @param SerializerProvider $serializer
     * @param PlaceholderManager $placeholderManager
     */
    public function __construct(
        SerializerProvider $serializer,
        PlaceholderManager $placeholderManager
    ) {
        $this->serializer = $serializer;
        $this->placeholderManager = $placeholderManager;
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
            'placeholders' => $this->placeholderManager->getAvailablePlaceholders(),
        ]);

        $event->stopPropagation();
    }
}
