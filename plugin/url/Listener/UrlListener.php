<?php

namespace HeVinci\UrlBundle\Listener;

use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use HeVinci\UrlBundle\Entity\Url;
use HeVinci\UrlBundle\Serializer\UrlSerializer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class UrlListener
{
    /** @var UrlSerializer */
    private $serializer;

    /**
     * UrlListener constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.serializer.url")
     * })
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
     * @DI\Observe("resource.hevinci_url.load")
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
     * @DI\Observe("delete_hevinci_url")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }
}
