<?php

namespace Claroline\SlideshowBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\SlideshowBundle\Entity\Resource\Slideshow;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Used to integrate Slideshow to Claroline resource manager.
 *
 * @DI\Service()
 */
class SlideshowListener
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * SlideshowListener constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
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
     * @DI\Observe("resource.claro_slideshow.load")
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
     * @DI\Observe("delete_claro_slideshow")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        // TODO : implement

        $event->stopPropagation();
    }
}
