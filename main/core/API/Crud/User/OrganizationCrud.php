<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Event\Crud\DeleteEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.crud.organization")
 * @DI\Tag("claroline.crud")
 */
class OrganizationCrud
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
     * @DI\Observe("crud_pre_create_object_claroline_corebundle_entity_organization_organization")
     *
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        $organization = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();
        $organization->addAdministrator($user);
    }

    /**
     * @DI\Observe("crud_pre_delete_object_claroline_corebundle_entity_organization_organization")
     *
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        /** @var Organization $organization */
        $organization = $event->getObject();
        if ($organization->isDefault()) {
            $event->block();

            // we can also throw an exception
        }
    }
}
