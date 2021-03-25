<?php

namespace Icap\BibliographyBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;

class BibliographyListener
{
    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * BibliographyListener constructor.
     */
    public function __construct(
        ObjectManager $objectManager,
        SerializerProvider $serializer
    ) {
        $this->serializer = $serializer;
        $this->om = $objectManager;
    }

    /**
     * Loads a Bibliography resource.
     */
    public function load(LoadResourceEvent $event)
    {
        $event->setData([
            'bookReference' => $this->serializer->serialize($event->getResource()),
        ]);

        $event->stopPropagation();
    }
}
