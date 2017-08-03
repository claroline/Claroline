<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
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
     * @var StrictDispatcher
     */
    private $eventDispatcher;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ResourceNodeSerializer
     */
    private $serializer;

    /**
     * @var RightsManager
     */
    private $rightsManager;

    /**
     * @var ResourceManager
     */
    private $resourceManager;

    /**
     * ResourceNodeManager constructor.
     *
     * @DI\InjectParams({
     *     "authorization"          = @DI\Inject("security.authorization_checker"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "resourceNodeSerializer" = @DI\Inject("claroline.serializer.resource_node"),
     *     "rightsManager"          = @DI\Inject("claroline.manager.rights_manager"),
     *     "resourceManager"        = @DI\Inject("claroline.manager.resource_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param StrictDispatcher              $eventDispatcher
     * @param ObjectManager                 $om
     * @param ResourceNodeSerializer        $resourceNodeSerializer
     * @param RightsManager                 $rightsManager
     * @param ResourceManager               $resourceManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $eventDispatcher,
        ObjectManager $om,
        ResourceNodeSerializer $resourceNodeSerializer,
        RightsManager $rightsManager,
        ResourceManager $resourceManager)
    {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->serializer = $resourceNodeSerializer; // todo : load from the SerializerProvider
        $this->rightsManager = $rightsManager;
        $this->resourceManager = $resourceManager;
    }

    /**
     * Serializes a ResourceNode entity for the JSON api.
     *
     * @param ResourceNode $resourceNode - the node to serialize
     *
     * @return array - the serialized representation of the node
     */
    public function serialize(ResourceNode $resourceNode)
    {
        return $this->serializer->serialize($resourceNode);
    }

    /**
     * Updates a ResourceNode entity.
     *
     * @param array        $data
     * @param ResourceNode $resourceNode
     *
     * @return ResourceNode
     *
     * @throws InvalidDataException
     */
    public function update(array $data, ResourceNode $resourceNode)
    {
        $errors = $this->validate($data);

        if (count($errors) > 0) {
            throw new InvalidDataException('ResourceNode data are invalid.', $errors);
        }

        if ($data['name'] !== $resourceNode->getName()) {
            $this->resourceManager->rename($resourceNode, $data['name'], true);
        }

        $this->updateMeta($data['meta'], $resourceNode);
        $this->updateParameters($data['parameters'], $resourceNode);
        $this->updateRights($data['rights']['all']['permissions'], $resourceNode);
        $this->om->persist($resourceNode);
        $this->om->flush();

        return $resourceNode;
    }

    private function updateMeta(array $meta, ResourceNode $resourceNode)
    {
        if ($meta['published'] !== $resourceNode->isPublished()) {
            $this->resourceManager->setPublishedStatus([$resourceNode], $meta['published']);
        }

        $resourceNode->setPublishedToPortal($meta['portal']);

        if (isset($meta['description'])) {
            $resourceNode->setDescription($meta['description']);
        }

        if (isset($meta['license'])) {
            $resourceNode->setLicense($meta['license']);
        }

        if (isset($meta['authors'])) {
            $resourceNode->setAuthor($meta['authors']);
        }
    }

    private function updateParameters(array $parameters, ResourceNode $resourceNode)
    {
        if (!empty($parameters['accessibleFrom'])) {
            $accessibleFrom = \DateTime::createFromFormat('Y-m-d\TH:i:s', $parameters['accessibleFrom']);
            $resourceNode->setAccessibleFrom($accessibleFrom);
        }

        if (!empty($parameters['accessibleUntil'])) {
            $accessibleUntil = \DateTime::createFromFormat('Y-m-d\TH:i:s', $parameters['accessibleUntil']);
            $resourceNode->setAccessibleUntil($accessibleUntil);
        }

        $resourceNode->setFullscreen($parameters['fullscreen']);
        $resourceNode->setClosable($parameters['closable']);
        $resourceNode->setCloseTarget($parameters['closeTarget']);
    }

    private function updateRights(array $rights, ResourceNode $resourceNode)
    {
        foreach ($rights as $rolePerms) {
            /** @var Role $role */
            $role = $this->om->getRepository('ClarolineCoreBundle:Role')->find($rolePerms['role']['id']);
            $this->rightsManager->editPerms($rolePerms['permissions'], $role, $resourceNode);
        }
    }

    /**
     * Validates data sent by API.
     *
     * @param array $data
     *
     * @return array
     */
    public function validate(array $data)
    {
        //json-schema ? à discuter
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = [
                'path' => '/name',
                'message' => 'name can not be empty',
            ];
        }

        if (!empty($parameters['accessibleFrom'])) {
            $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $parameters['accessibleFrom']);
            if (!$dateTime || $dateTime->format('Y-m-d\TH:i:s') !== $parameters['accessibleFrom']) {
                $errors[] = [
                    'path' => '/parameters/accessibleFrom',
                    'message' => 'Invalid date format',
                ];
            }
        }

        if (!empty($parameters['accessibleUntil'])) {
            $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $parameters['accessibleUntil']);
            if (!$dateTime || $dateTime->format('Y-m-d\TH:i:s') !== $parameters['accessibleUntil']) {
                $errors[] = [
                    'path' => '/parameters/accessibleUntil',
                    'message' => 'Invalid date format',
                ];
            }
        }

        return $errors;
    }

    public function publish(ResourceNode $resourceNode)
    {
        if (!$resourceNode->isPublished()) {
            $this->resourceManager->setPublishedStatus([$resourceNode], true);
        }

        return $resourceNode;
    }

    public function unpublish(ResourceNode $resourceNode)
    {
        if ($resourceNode->isPublished()) {
            $this->resourceManager->setPublishedStatus([$resourceNode], false);
        }

        return $resourceNode;
    }

    public function delete(ResourceNode $resourceNode)
    {
        $this->resourceManager->delete($resourceNode);
    }
}
