<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     *     "resourceManager"        = @DI\Inject("claroline.manager.resource_manager"),
     *     "session"                = @DI\Inject("session")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param StrictDispatcher              $eventDispatcher
     * @param ObjectManager                 $om
     * @param ResourceNodeSerializer        $resourceNodeSerializer
     * @param RightsManager                 $rightsManager
     * @param ResourceManager               $resourceManager
     * @param SessionInterface              $session
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $eventDispatcher,
        ObjectManager $om,
        ResourceNodeSerializer $resourceNodeSerializer,
        RightsManager $rightsManager,
        ResourceManager $resourceManager,
        SessionInterface $session
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->serializer = $resourceNodeSerializer; // todo : load from the SerializerProvider
        $this->rightsManager = $rightsManager;
        $this->resourceManager = $resourceManager;
        $this->session = $session;
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

        if (isset($data['poster'])) {
            $resourceNode->setPoster($data['poster']['url']);
        }

        //why no deserialize from serializer ?
        //@todo move in serializer

        $this->updateMeta($data['meta'], $resourceNode);
        $this->updateDisplay($data['display'], $resourceNode);
        $this->updateRestrictions($data['restrictions'], $resourceNode);
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

    private function updateDisplay(array $parameters, ResourceNode $resourceNode)
    {
        $resourceNode->setFullscreen($parameters['fullscreen']);
        $resourceNode->setClosable($parameters['closable']);
        $resourceNode->setCloseTarget($parameters['closeTarget']);
    }

    private function updateRestrictions(array $restrictions, ResourceNode $resourceNode)
    {
        if (isset($restrictions['dates'])) {
            $dateRange = DateRangeNormalizer::denormalize($restrictions['dates']);

            $resourceNode->setAccessibleFrom($dateRange[0]);
            $resourceNode->setAccessibleUntil($dateRange[1]);
        }

        if (isset($restrictions['code'])) {
            $resourceNode->setAccessCode($restrictions['code']);
        }

        if (isset($restrictions['ips'])) {
            $resourceNode->setAllowedIps($restrictions['ips']);
        }
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

    public function unlock(ResourceNode $resourceNode, $code)
    {
        //if a code is defined
        if ($accessCode = $resourceNode->getAccessCode()) {
            if ($accessCode === $code) {
                $this->session->set($resourceNode->getGuid(), true);

                return true;
            } else {
                $this->session->set($resourceNode->getGuid(), false);

                return false;
            }
        }

        return true;
    }

    public function isCodeProtected(ResourceNode $resourceNode)
    {
        return !empty($resourceNode->getAccessCode());
    }

    public function requiresUnlock(ResourceNode $resourceNode)
    {
        $isProtected = $this->isCodeProtected($resourceNode);

        if ($isProtected) {
            return !$this->isUnlocked($resourceNode);
        }

        return false;
    }

    public function isUnlocked(ResourceNode $node)
    {
        if ($node->getAccessCode()) {
            $access = $this->session->get($node->getGuid());

            return null !== $access ? $access : false;
        }

        return true;
    }

    public function addView(ResourceNode $node)
    {
        $node->addView();
        $this->om->persist($node);
        $this->om->flush();

        return $node;
    }
}
