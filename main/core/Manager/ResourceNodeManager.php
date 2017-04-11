<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.manager.resource_node")
 */
class ResourceNodeManager
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * ResourceNodeManager constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Serializes a ResourceNode entity for the JSON api.
     *
     * @param ResourceNode $resourceNode - the node to serialize
     * @param User         $currentUser  - the current user to know what to export
     *
     * @return array - the serialized representation of the node
     */
    public function serialize(ResourceNode $resourceNode, User $currentUser = null)
    {
        $collection = new ResourceCollection([$resourceNode]);

        return [
            'id' => $resourceNode->getGuid(),
            'name' => $resourceNode->getName(),
            'mimeType' => $resourceNode->getMimeType(),
            'type' => $resourceNode->getResourceType()->getName(),
            'poster' => null,
            'meta' => [
                'created' => $resourceNode->getCreationDate()->format('Y-m-d\TH:i:s'),
                'updated' => $resourceNode->getModificationDate()->format('Y-m-d\TH:i:s'),
                'license' => $resourceNode->getLicense(),
                'published' => $resourceNode->isPublished(),
                'portal' => $resourceNode->isPublishedToPortal(),
                'exportable' => $this->authorization->isGranted('EXPORT', $collection) && $resourceNode->getResourceType()->isExportable(),
                'editable' => $this->authorization->isGranted('ADMINISTRATE', $collection),
                'deletable' => $this->authorization->isGranted('DELETE', $collection),
                'authors' => [[
                    'id' => $resourceNode->getCreator()->getGuid(),
                    'name' => $resourceNode->getCreator()->getFullName(),
                    'username' => $resourceNode->getCreator()->getUsername(),
                ]],
                'workspace' => [
                    'id' => $resourceNode->getWorkspace()->getGuid(),
                    'name' => $resourceNode->getWorkspace()->getName(),
                    'code' => $resourceNode->getWorkspace()->getCode(),
                ],
                'actions' => [

                ],
            ],
            'parameters' => [
                'accessibleFrom' => null,
                'accessibleUntil' => null,
                'fullscreen' => false,
                'closeTarget' => '',
            ],
            'rights' => [

            ],
            'tags' => [// it comes from a plugin

            ],
            'social' => [ // it comes from a plugin
                'likes' => 100,
                'comments' => 5,
            ],
            'user' => [
                'favorite' => true, // it comes from a plugin
                'like' => true, // it comes from a plugin
                'notes' => [], // it comes from a plugin
            ],
        ];
    }

    public function publish(ResourceNode $resourceNode)
    {
        $resourceNode->setPublished(true);
        $resource = $this->resourceManager->getResourceFromNode($resourceNode);
        $this->dispatcher->dispatch(
            'publication_change_'.$resourceNode->getResourceType()->getName(),
            'PublicationChange',
            [$resource]
        );

        $usersToNotify = $resourceNode->getWorkspace() ?
            $this->userManager->getUsersByWorkspaces([$resourceNode->getWorkspace()], null, null, false) :
            [];

        $this->dispatcher->dispatch('log', 'Log\LogResourcePublish', [$resourceNode, $usersToNotify]);

        $this->om->flush();
    }

    public function delete(ResourceNode $resourceNode)
    {
    }
}
