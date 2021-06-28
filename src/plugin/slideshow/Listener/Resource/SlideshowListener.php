<?php

namespace Claroline\SlideshowBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\SlideshowBundle\Entity\Resource\Slideshow;

/**
 * Used to integrate Slideshow to Claroline resource manager.
 */
class SlideshowListener
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * SlideshowListener constructor.
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Loads the Slideshow resource.
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Slideshow $slideshow */
        $slideshow = $event->getResource();

        $event->setData([
            'slideshow' => $this->serializer->serialize($slideshow),
        ]);
        $event->stopPropagation();
    }
}
