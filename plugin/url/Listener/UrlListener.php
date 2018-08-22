<?php

namespace HeVinci\UrlBundle\Listener;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
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
    private $om;
    private $serlaizer;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     * })
     */
    public function __construct(
        SerializerProvider $serializer,
        ObjectManager $om
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
    }

    /**
     * @DI\Observe("delete_hevinci_url")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $this->om->remove($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_hevinci_url")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $resource = $event->getResource();
        $copy = new Url();
        $copy->setName($resource->getName());
        $copy->setUrl($resource->getUrl());
        $copy->setInternalUrl($resource->getInternalUrl());

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * Loads a Wiki resource.
     *
     * @DI\Observe("resource.hevinci_url.load")
     *
     * @param LoadResourceEvent $event
     */
    public function load(LoadResourceEvent $event)
    {
        /** @var Url $url */
        $url = $event->getResource();

        $event->setData([
            'url' => $this->serializer->serialize($url),
        ]);

        $event->stopPropagation();
    }
}
