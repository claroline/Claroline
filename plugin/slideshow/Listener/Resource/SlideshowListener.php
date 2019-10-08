<?php

namespace Claroline\SlideshowBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
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
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Loads the Slideshow resource.
     *
     * @param LoadResourceEvent $event
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

    /**
     * Fired when a ResourceNode of type Slideshow is deleted.
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        // TODO : implement

        $event->stopPropagation();
    }
}
