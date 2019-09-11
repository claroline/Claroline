<?php

namespace Claroline\CoreBundle\API\Crud\Resource;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @todo manage correct renaming : see $this->resourceManager->rename($resourceNode, $data['name'], true);
 * @todo correct manage publication see : $this->resourceManager->setPublishedStatus([$resourceNode], $meta['published']);
 */
class ResourceNodeCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * ResourceNodeCrud constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param CreateEvent $event
     *
     * @return ResourceNode
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $event->getObject();

        // set the creator of the resource
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            $resourceNode->setCreator($user);
        }

        return $resourceNode;
    }
}
