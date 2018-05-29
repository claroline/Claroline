<?php

namespace Claroline\CoreBundle\API\Crud\Resource;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.crud.resource_node")
 * @DI\Tag("claroline.crud")
 *
 * @todo manage correct renaming : see $this->resourceManager->rename($resourceNode, $data['name'], true);
 * @todo correct manage publication see : $this->resourceManager->setPublishedStatus([$resourceNode], $meta['published']);
 */
class ResourceNodeCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ResourceManager */
    private $resourceManager;

    /**
     * ResourceNodeCrud constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param ResourceManager       $resourceManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ResourceManager $resourceManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->resourceManager = $resourceManager;
    }

    /**
     * @DI\Observe("crud_pre_create_object_claroline_corebundle_entity_resource_resource_node")
     *
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

    /**
     * @DI\Observe("crud_pre_update_object_claroline_corebundle_entity_resource_resource_node")
     *
     * @param UpdateEvent $event
     */
    public function preUpdate(UpdateEvent $event)
    {
        /*$workspace = $event->getObject();
        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        $root->setName($workspace->getName());*/

        /*$this->om->persist($root);
        $this->om->flush();*/
    }
}
