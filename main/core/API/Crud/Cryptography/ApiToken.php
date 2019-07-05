<?php

namespace Claroline\CoreBundle\API\Crud\Cryptography;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.crud.cryptography.api_token")
 * @DI\Tag("claroline.crud")
 */
class ApiToken
{
    /**
     * @DI\InjectParams({
     *     "tokenStorage"           = @DI\Inject("security.token_storage")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @DI\Observe("crud_pre_create_object_claroline_corebundle_entity_cryptography_apitoken")
     *
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var Role $role */
        $apiToken = $event->getObject();
        $apiToken->setUser($this->tokenStorage->getToken()->getUser());
    }
}
