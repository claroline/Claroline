<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\ResourceComment;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Resource\DecorateResourceNodeEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\Resource\RightsManager;

class ResourceNodeSerializer
{
    use SerializerTrait;

    const NO_PARENT = 'no_parent';

    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $eventDispatcher;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var UserSerializer */
    private $userSerializer;
    /** @var RightsManager */
    private $rightsManager;
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        PublicFileSerializer $fileSerializer,
        UserSerializer $userSerializer,
        RightsManager $rightsManager,
        SerializerProvider $serializer
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileSerializer = $fileSerializer;
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
        $serializedNode = [
            'id' => $resourceNode->getUuid(),
            'autoId' => $resourceNode->getId(),
            'slug' => $resourceNode->getSlug(),
            'name' => $resourceNode->getName(),
            'path' => $resourceNode->getAncestors(),
            'meta' => $this->serializeMeta($resourceNode, $options),
            'permissions' => $this->rightsManager->getCurrentPermissionArray($resourceNode),
            'thumbnail' => $this->serializeThumbnail($resourceNode),
            'evaluation' => [
                'evaluated' => $resourceNode->isEvaluated(),
                'required' => $resourceNode->isRequired(),
            ],
        ];

        if ($resourceNode->getWorkspace()) {
            $serializedNode['workspace'] = [ // TODO : use workspace serializer with minimal option
                'id' => $resourceNode->getWorkspace()->getUuid(),
                'autoId' => $resourceNode->getWorkspace()->getId(),
                'slug' => $resourceNode->getWorkspace()->getSlug(),
                'name' => $resourceNode->getWorkspace()->getName(),
                'code' => $resourceNode->getWorkspace()->getCode(),
            ];
        }

        $parent = $resourceNode->getParent();
        if (!empty($parent) && !in_array(static::NO_PARENT, $options)) {
            $serializedNode['parent'] = $this->serialize($resourceNode->getParent(), [Options::SERIALIZE_MINIMAL, static::NO_PARENT]);
        }

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serializedNode['poster'] = $this->serializePoster($resourceNode);
            $serializedNode['restrictions'] = $this->serializeRestrictions($resourceNode);

            if (!in_array(Options::SERIALIZE_LIST, $options)) {
                $serializedNode = array_merge($serializedNode, [
                    'display' => $this->serializeDisplay($resourceNode),
                    'comments' => array_map(function (ResourceComment $comment) { // TODO : should not be exposed here
                        return $this->serializer->serialize($comment);
                    }, $resourceNode->getComments()->toArray()),
                ]);
            }

            if (!in_array(Options::NO_RIGHTS, $options)) {
                // export rights, only used by transfer feature. Should be moved later.
                $serializedNode['rights'] = array_values($this->rightsManager->getRights($resourceNode));
            }
        }

        return $this->decorate($resourceNode, $serializedNode, $options);
    }

    /**
     * Dispatches an event to let plugins add some custom data to the serialized node.
     * For example, SocialMedia adds the number of likes.
     */
    private function decorate(ResourceNode $resourceNode, array $serializedNode, array $options = []): array
    {
        // avoid plugins override the standard node properties
        $unauthorizedKeys = array_keys($serializedNode);

        // 'thumbnail' is a key that can be overridden by another plugin. For example: UrlBundle
        // TODO : find a cleaner way to do it
        $key = array_search('thumbnail', $unauthorizedKeys);
        if (false !== $key) {
            unset($unauthorizedKeys[$key]);
        }

        /** @var DecorateResourceNodeEvent $event */
        $event = $this->eventDispatcher->dispatch('serialize_resource_node', 'Resource\DecorateResourceNode', [
            $resourceNode,
            $unauthorizedKeys,
            $options,
        ]);

        return array_merge($serializedNode, $event->getInjectedData());
    }

    /**
     * Serialize the resource poster.
     */
    private function serializePoster(ResourceNode $resourceNode): ?array
    {
        if (!empty($resourceNode->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $resourceNode->getPoster()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    /**
     * Serialize the resource thumbnail.
     */
    private function serializeThumbnail(ResourceNode $resourceNode): ?array
    {
        if (!empty($resourceNode->getThumbnail())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $resourceNode->getThumbnail()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    private function serializeMeta(ResourceNode $resourceNode, array $options): array
    {
        $meta = [
            'type' => $resourceNode->getResourceType()->getName(),
            'className' => $resourceNode->getResourceType()->getClass(),
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
            'commentsActivated' => $resourceNode->isCommentsActivated(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $meta = array_merge($meta, [
                'authors' => $resourceNode->getAuthor(),
                'license' => $resourceNode->getLicense(),
            ]);
        }

        return $meta;
    }

    private function serializeDisplay(ResourceNode $resourceNode): array
    {
        return [
            'fullscreen' => $resourceNode->isFullscreen(),
            'showIcon' => $resourceNode->getShowIcon(),
            'showTitle' => $resourceNode->getShowTitle(),
        ];
    }

    private function serializeRestrictions(ResourceNode $resourceNode): array
    {
        return [
            'hidden' => $resourceNode->isHidden(),
            'dates' => DateRangeNormalizer::normalize(
                $resourceNode->getAccessibleFrom(),
                $resourceNode->getAccessibleUntil()
            ),
            'code' => $resourceNode->getAccessCode(),
            'allowedIps' => $resourceNode->getAllowedIps(),
        ];
    }

    /**
     * Deserializes resource node data into entities.
     */
    public function deserialize(array $data, ResourceNode $resourceNode, array $options = []): ResourceNode
    {
        $this->sipe('name', 'setName', $data, $resourceNode);

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

        if (isset($data['poster']) && isset($data['poster']['url'])) {
            $resourceNode->setPoster($data['poster']['url']);
        }

        if (isset($data['thumbnail']) && isset($data['thumbnail']['url'])) {
            $resourceNode->setThumbnail($data['thumbnail']['url']);
        }

        // meta
        if (empty($resourceNode->getResourceType())) {
            /** @var ResourceType $resourceType */
            $resourceType = $this->om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
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
        }

        if (!in_array(Options::NO_RIGHTS, $options) && isset($data['rights'])) {
            // only used by transfer feature and creation. Should be moved later
            $this->deserializeRights($data['rights'], $resourceNode, $options);
        }

        return $resourceNode;
    }

    public function deserializeRights($rights, ResourceNode $resourceNode, array $options = [])
    {
        $existingRights = $resourceNode->getRights();

        $roles = [];
        foreach ($rights as $right) {
            // this block is required by workspace
            if (!in_array(Options::REFRESH_UUID, $options)) {
                /** @var Role $role */
                $role = $this->om->getRepository(Role::class)->findOneBy(['name' => $right['name']]);
            } else {
                // this block is required by workspace transfer and I don't know why (it shouldn't)
                $workspace = $resourceNode->getWorkspace() ?
                    $resourceNode->getWorkspace() :
                    $this->om->getRepository(Workspace::class)->findOneBy(['code' => $right['workspace']['code']]);

                /** @var Role $role */
                $role = $this->om->getRepository(Role::class)->findOneBy([
                    'translationKey' => $right['translationKey'],
                    'workspace' => $workspace,
                ]);
            }

            if ($role) {
                $creationPerms = [];
                if (isset($right['permissions']['create'])) {
                    if (!empty($right['permissions']['create']) && 'directory' === $resourceNode->getResourceType()->getName()) {
                        // ugly hack to only get create rights for directories (it's the only one that can handle it).
                        $creationPerms = array_map(function (string $typeName) {
                            return $this->om
                                ->getRepository(ResourceType::class)
                                ->findOneBy(['name' => $typeName]);
                        }, $right['permissions']['create']);
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
