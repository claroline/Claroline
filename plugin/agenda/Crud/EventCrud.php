<?php

namespace Claroline\AgendaBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.crud.agenda_event")
 * @DI\Tag("claroline.crud")
 */
class EventCrud
{
    /**
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @DI\Observe("crud_pre_create_object_claroline_agendabundle_entity_event")
     *
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var Group $role */
        $object = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            $object->setUser($user);
        }
    }
}
