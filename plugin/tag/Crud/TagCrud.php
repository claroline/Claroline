<?php

namespace Claroline\TagBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\TagBundle\Entity\Tag;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.crud.tag")
 * @DI\Tag("claroline.crud")
 */
class TagCrud
{
    /**
     * TagCrud constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @DI\Observe("crud_pre_create_object_claroline_tagbundle_entity_tag")
     *
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var Tag $tag */
        $tag = $event->getObject();

        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            $tag->setUser($user);
        }
    }
}
