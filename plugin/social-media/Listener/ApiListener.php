<?php

namespace Icap\SocialmediaBundle\Listener;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\DecorateResourceNodeEvent;

/**
 * Class ApiListener.
 */
class ApiListener
{
    /** @var ObjectManager */
    private $om;

    /**
     * ApiListener constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Add like count to serialized resource node when requested through API.
     *
     * @param DecorateResourceNodeEvent $event
     */
    public function onSerialize(DecorateResourceNodeEvent $event)
    {
        $count = $this->om->getRepository('IcapSocialmediaBundle:LikeAction')->countLikes([
            'resource' => $event->getResourceNode()->getId(),
        ]);

        $event->add('social', [
            'likes' => $count,
        ]);
    }
}
