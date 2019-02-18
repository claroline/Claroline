<?php

namespace Claroline\RssBundle\Listener;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\RssBundle\Entity\RssFeed;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class RssListener
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
     * @DI\Observe("resource.rss_feed.load")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        $rss = $event->getResource();

        $event->setData([
            'rssFeed' => $this->serializer->serialize($rss),
        ]);

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_rss_feed")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_rss_feed")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Url $resource */
        $resource = $event->getResource();

        $copy = new RssFeed();
        $copy->setUrl($resource->getUrl());

        $event->setCopy($copy);
        $event->stopPropagation();
    }
}
