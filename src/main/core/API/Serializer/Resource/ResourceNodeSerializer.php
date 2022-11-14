<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceComment;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Resource\DecorateResourceNodeEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ResourceNodeSerializer
{
    use SerializerTrait;

    const NO_PARENT = 'no_parent';

    /** @var ObjectManager */
    private $om;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var RightsManager */
    private $rightsManager;
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        UserSerializer $userSerializer,
        RightsManager $rightsManager,
        SerializerProvider $serializer
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->userSerializer = $userSerializer;
        $this->rightsManager = $rightsManager;
        $this->serializer = $serializer;
    }

    public function getClass(): string
    {
        return ResourceNode::class;
    }

    public function getName(): string
    {
        return 'resource_node';
    }

    public function getSchema(): string
    {
        return '#/main/core/resource/resource-node.json';
    }

    /**
     * Serializes a ResourceNode entity for the JSON api.
     */
    public function serialize(ResourceNode $resourceNode, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $resourceNode->getUuid(),
                'slug' => $resourceNode->getSlug(),
                'name' => $resourceNode->getName(),
                'thumbnail' => $resourceNode->getThumbnail(),
                'meta' => [
                    'published' => $resourceNode->isPublished(), // not required but nice to have
                    // move outside meta
                    'type' => $resourceNode->getType(), // try to remove. use mimeType instead
                    'mimeType' => $resourceNode->getMimeType(),
                ],
            ];
        }

        $serializedNode = [
            'id' => $resourceNode->getUuid(),
            'autoId' => $resourceNode->getId(),
            'slug' => $resourceNode->getSlug(),
            'name' => $resourceNode->getName(),
            'path' => $resourceNode->getAncestors(),
            'meta' => [
                'type' => $resourceNode->getType(), // try to remove. use mimeType instead
                'className' => $resourceNode->getClass(), // try to remove. use mimeType instead
                'mimeType' => $resourceNode->getMimeType(),
                'description' => $resourceNode->getDescription(),
                'creator' => $resourceNode->getCreator() ?
                    $this->userSerializer->serialize($resourceNode->getCreator(), [Options::SERIALIZE_MINIMAL]) :
                    null,
                'created' => DateNormalizer::normalize($resourceNode->getCreationDate()),
                'updated' => DateNormalizer::normalize($resourceNode->getModificationDate()),
                'published' => $resourceNode->isPublished(),
                'active' => $resourceNode->isActive(),
                'views' => $resourceNode->getViewsCount(),
                'authors' => $resourceNode->getAuthor(),
                'license' => $resourceNode->getLicense(),
                'commentsActivated' => $resourceNode->isCommentsActivated(),
            ],
            'thumbnail' => $resourceNode->getThumbnail(),
            'poster' => $resourceNode->getPoster(),
            'evaluation' => [
                'evaluated' => $resourceNode->isEvaluated(),
                'required' => $resourceNode->isRequired(),
                'estimatedDuration' => $resourceNode->getEstimatedDuration(),
            ],
            'restrictions' => [
                'hidden' => $resourceNode->isHidden(),
                'dates' => DateRangeNormalizer::normalize($resourceNode->getAccessibleFrom(), $resourceNode->getAccessibleUntil()),
                'code' => $resourceNode->getAccessCode(),
                'allowedIps' => $resourceNode->getAllowedIps(),
            ],
            'tags' => $this->serializeTags($resourceNode),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serializedNode['permissions'] = $this->rightsManager->getCurrentPermissionArray($resourceNode);
        }

        if ($resourceNode->getWorkspace()) {
            $serializedNode['workspace'] = [ // TODO : use workspace serializer with minimal option
                'id' => $resourceNode->getWorkspace()->getUuid(),
                'autoId' => $resourceNode->getWorkspace()->getId(),
                'slug' => $resourceNode->getWorkspace()->getSlug(),
                'name' => $resourceNode->getWorkspace()->getName(),
                'code' => $resourceNode->getWorkspace()->getCode(),
                'thumbnail' => $resourceNode->getWorkspace()->getThumbnail(),
            ];
        }

        if (!empty($resourceNode->getParent())) {
            $serializedNode['parent'] = $this->serialize($resourceNode->getParent(), [Options::SERIALIZE_MINIMAL]);
        }

        if (!in_array(Options::SERIALIZE_LIST, $options)) {
            $serializedNode = array_merge($serializedNode, [
                'display' => [
                    'fullscreen' => $resourceNode->isFullscreen(),
                    'showIcon' => $resourceNode->getShowIcon(),
                    'showTitle' => $resourceNode->getShowTitle(),
                ],
                'comments' => array_map(function (ResourceComment $comment) { // TODO : should not be exposed here
                    return $this->serializer->serialize($comment);
                }, $resourceNode->getComments()->toArray()),
            ]);
        }

        if (!in_array(Options::NO_RIGHTS, $options)) {
            // export rights, only used by transfer feature. Should be moved later.
            $serializedNode['rights'] = array_values($this->rightsManager->getRights($resourceNode));
        }

        return $this->decorate($resourceNode, $serializedNode, $options);
    }

    /**
     * Dispatches an event to let plugins add some custom data to the serialized node.
     * For example, Notification adds a flag to know if the current user follows the resource.
     */
    private function decorate(ResourceNode $resourceNode, array $serializedNode, array $options = []): array
    {
        // avoid plugins override the standard node properties
        $unauthorizedKeys = array_keys($serializedNode);

        $event = new DecorateResourceNodeEvent($resourceNode, $unauthorizedKeys, $options);
        $this->eventDispatcher->dispatch($event, 'serialize_resource_node');

        return array_merge($serializedNode, $event->getInjectedData());
    }

    private function serializeTags(ResourceNode $resourceNode): array
    {
        $event = new GenericDataEvent([
            'class' => ResourceNode::class,
            'ids' => [$resourceNode->getUuid()],
        ]);

        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    /**
     * Deserializes resource node data into entities.
     */
    public function deserialize(array $data, ResourceNode $resourceNode, array $options = []): ResourceNode
    {
        $this->sipe('name', 'setName', $data, $resourceNode);
        $this->sipe('poster', 'setPoster', $data, $resourceNode);
        $this->sipe('thumbnail', 'setThumbnail', $data, $resourceNode);

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $resourceNode);
            $this->sipe('slug', 'setSlug', $data, $resourceNode);
        } else {
            $resourceNode->refreshUuid();
        }

        if (isset($data['workspace'])) {
            /** @var Workspace $workspace */
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $data['workspace']['id']]);
            $resourceNode->setWorkspace($workspace);
        }

        // meta
        if (empty($resourceNode->getResourceType())) {
            /** @var ResourceType $resourceType */
            $resourceType = $this->om
                ->getRepository(ResourceType::class)
                ->findOneBy(['name' => $data['meta']['type']]);

            $resourceNode->setResourceType($resourceType);
        }

        if (empty($resourceNode->getMimeType())) {
            if (isset($data['meta']) && !empty($data['meta']['mimeType'])) {
                $mimeType = $data['meta']['mimeType'];
            } else {
                $mimeType = 'custom/'.$resourceNode->getResourceType()->getName();
            }

            $resourceNode->setMimeType($mimeType);
        }

        $this->sipe('meta.published', 'setPublished', $data, $resourceNode);
        $this->sipe('meta.description', 'setDescription', $data, $resourceNode);
        $this->sipe('meta.license', 'setLicense', $data, $resourceNode);
        $this->sipe('meta.authors', 'setAuthor', $data, $resourceNode);
        $this->sipe('meta.commentsActivated', 'setCommentsActivated', $data, $resourceNode);

        // display
        $this->sipe('display.fullscreen', 'setFullscreen', $data, $resourceNode);
        $this->sipe('display.showIcon', 'setShowIcon', $data, $resourceNode);
        $this->sipe('display.showTitle', 'setShowTitle', $data, $resourceNode);

        // restrictions
        if (isset($data['restrictions'])) {
            $this->sipe('restrictions.code', 'setAccessCode', $data, $resourceNode);
            $this->sipe('restrictions.allowedIps', 'setAllowedIps', $data, $resourceNode);
            $this->sipe('restrictions.hidden', 'setHidden', $data, $resourceNode);

            if (isset($data['restrictions']['dates'])) {
                $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

                $resourceNode->setAccessibleFrom($dateRange[0]);
                $resourceNode->setAccessibleUntil($dateRange[1]);
            }
        }

        if (isset($data['evaluation'])) {
            $this->sipe('evaluation.evaluated', 'setEvaluated', $data, $resourceNode);
            $this->sipe('evaluation.required', 'setRequired', $data, $resourceNode);
            $this->sipe('evaluation.estimatedDuration', 'setEstimatedDuration', $data, $resourceNode);
        }

        if (!in_array(Options::NO_RIGHTS, $options) && isset($data['rights'])) {
            // only used to be able to directly create a node with rights. Used in transfer feature and ui creation. To move later
            $this->deserializeRights($data['rights'], $resourceNode, $options);
        }

        return $resourceNode;
    }

    public function deserializeRights($rights, ResourceNode $resourceNode, array $options = [])
    {
        $existingRights = $resourceNode->getRights();

        $roles = [];
        foreach ($rights as $right) {
            $role = $this->om->getRepository(Role::class)->findOneBy(['name' => $right['name']]);
            if ($role) {
                $creationPerms = [];
                if (isset($right['permissions']['create'])) {
                    if (!empty($right['permissions']['create']) && 'directory' === $resourceNode->getResourceType()->getName()) {
                        // ugly hack to only get create rights for directories (it's the only one that can handle it).
                        $creationPerms = array_filter(array_map(function (string $typeName) {
                            return $this->om
                                ->getRepository(ResourceType::class)
                                ->findOneBy(['name' => $typeName]);
                        }, $right['permissions']['create']), function ($type) {
                            return !empty($type);
                        });
                    }

                    unset($right['permissions']['create']);
                }

                // this should not be done here, because it will do db changes
                $this->rightsManager->update(
                    $right['permissions'],
                    $role,
                    $resourceNode,
                    in_array(Options::IS_RECURSIVE, $options),
                    $creationPerms
                );

                $roles[] = $role->getName();
            }
        }

        // removes rights which no longer exists
        foreach ($existingRights as $existingRight) {
            if (!in_array($existingRight->getRole()->getName(), $roles)) {
                $resourceNode->removeRight($existingRight);
            }
        }
    }
}
