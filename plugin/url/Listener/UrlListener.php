<?php

namespace HeVinci\UrlBundle\Listener;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use HeVinci\UrlBundle\Entity\Url;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class UrlListener
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * UrlListener constructor.
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

    /**
     * @DI\Observe("copy_hevinci_url")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Url $resource */
        $resource = $event->getResource();

        $copy = new Url();
        $copy->setName($resource->getName());
        $copy->setUrl($resource->getUrl());
        $copy->setInternalUrl($resource->getInternalUrl());

        $event->setCopy($copy);
        $event->stopPropagation();
    }
}
