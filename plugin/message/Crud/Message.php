<?php

namespace Claroline\MessageBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\MessageBundle\Manager\MessageManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.messaging.message")
 * @DI\Tag("claroline.crud")
 */
class Message
{
    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.message_manager"),
     * })
     *
     * @param MessageManager $manager
     */
    public function __construct(MessageManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("crud_post_create_object_claroline_messagebundle_entity_message")
     *
     * @param CreateEvent $event
     */
    public function postCreate(CreateEvent $event)
    {
        $this->manager->send($event->getObject());
    }
}
