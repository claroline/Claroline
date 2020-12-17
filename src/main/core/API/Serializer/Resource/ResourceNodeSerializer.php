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
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Claroline\CoreBundle\Manager\Resource\OptimizedRightsManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\LinkBundle\Entity\Resource\Shortcut;

class ResourceNodeSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var PublicFileSerializer */
    private $fileSerializer;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var OptimizedRightsManager */
    private $newRightsManager;

    /** @var RightsManager */
    private $rightsManager;

    /** @var MaskManager */
    private $maskManager;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * ResourceNodeManager constructor.
     *
     * @param ObjectManager          $om
     * @param StrictDispatcher       $eventDispatcher
     * @param PublicFileSerializer   $fileSerializer
     * @param UserSerializer         $userSerializer
     * @param MaskManager            $maskManager
     * @param OptimizedRightsManager $newRightsManager
     * @param RightsManager          $rightsManager,
     * @param SerializerProvider     $serializer
     */
    public function __construct(
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        PublicFileSerializer $fileSerializer,
        UserSerializer $userSerializer,
        MaskManager $maskManager,
        OptimizedRightsManager $newRightsManager,
        RightsManager $rightsManager,
        SerializerProvider $serializer
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileSerializer = $fileSerializer;
        $this->userSerializer = $userSerializer;
        $this->newRightsManager = $newRightsManager;
        $this->maskManager = $maskManager;
        $this->rightsManager = $rightsManager;
        $this->serializer = $serializer;
    }

    public function getName()
    {
        return 'resource_node';
    }

    /**
     * Serializes a ResourceNode entity for the JSON api.
     *
     * @param ResourceNode $resourceNode - the node to serialize
     * @param array        $options
     *
     * @return array - the serialized representation of the node
     */
    public function serialize(ResourceNode $resourceNode, array $options = [])
    {
        $serializedNode = [
            'id' => $resourceNode->getUuid(),
            'autoId' => $resourceNode->getId(),
            'slug' => $resourceNode->getSlug(),
            'name' => $resourceNode->getName(),
            'path' => $resourceNode->getAncestors(),
            'meta' => $this->serializeMeta($resourceNode, $options),
            'permissions' => $this->rightsManager->getCurrentPermissionArray($resourceNode),
            'poster' => $this->serializePoster($resourceNode),
            'thumbnail' => $this->serializeThumbnail($resourceNode),
            // TODO : it should not be available in minimal mode
            // for now I need it to compute simple access rights (for display)
            // we should compute simple access here to avoid exposing this big object
            'rights' => array_values($this->rightsManager->getRights($resourceNode, $options)),
        ];

        if ($resourceNode->getWorkspace() && !in_array(Options::REFRESH_UUID, $options)) {
            $serializedNode['workspace'] = [ // TODO : use workspace serializer with minimal option
                'id' => $resourceNode->getWorkspace()->getUuid(),
                'autoId' => $resourceNode->getWorkspace()->getId(),
                'slug' => $resourceNode->getWorkspace()->getSlug(),
                'name' => $resourceNode->getWorkspace()->getName(),
                'code' => $resourceNode->getWorkspace()->getCode(),
            ];
        }

        $parent = $resourceNode->getParent();
        if (!empty($parent)) {
            $serializedNode['parent'] = [
                'id' => $parent->getUuid(),
                'autoId' => $parent->getId(),
                'name' => $parent->getName(),
                'slug' => $parent->getSlug(),
            ];
        }

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serializedNode = array_merge($serializedNode, [
                'display' => $this->serializeDisplay($resourceNode),
                'restrictions' => $this->serializeRestrictions($resourceNode),
                'comments' => array_map(function (ResourceComment $comment) {
                    return $this->serializer->serialize($comment);
                }, $resourceNode->getComments()->toArray()),
            ]);
        }

        return $this->decorate($resourceNode, $serializedNode, $options);
    }

    /**
     * Dispatches an event to let plugins add some custom data to the serialized node.
     * For example, SocialMedia adds the number of likes.
     *
     * @param ResourceNode $resourceNode   - the original node entity
     * @param array        $serializedNode - the serialized version of the node
     * @param array        $options
     *
     * @return array - the decorated node
     */
    private function decorate(ResourceNode $resourceNode, array $serializedNode, array $options = [])
    {
        // avoid plugins override the standard node properties
        $unauthorizedKeys = array_keys($serializedNode);

        // 'thumbnail' is a key that can be overridden by another plugin. For example: UrlBundle
        // TODO : find a cleaner way to do it
        if (false !== ($key = array_search('thumbnail', $unauthorizedKeys))) {
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
     *
     * @param ResourceNode $resourceNode
     *
     * @return array|null
     */
    private function serializePoster(ResourceNode $resourceNode)
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
     *
     * @param ResourceNode $resourceNode
     *
     * @return array|null
     */
    private function serializeThumbnail(ResourceNode $resourceNode)
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

    private function serializeMeta(ResourceNode $resourceNode, array $options)
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

        if (Shortcut::class === $resourceNode->getResourceType()->getClass()) {
            // required for opening the proper player in case of shortcut. This is not pretty but the players
            // need the meta['type'] to be the target one to open the proper player/editor (they don't know what to do otherwise)
            // unless we implement a "link" player which will then the target and dispatch again.
            // This is the easy way
            /** @var Shortcut $resource */
            $resource = $this->om->getRepository($resourceNode->getClass())->findOneBy(['resourceNode' => $resourceNode]);
            $target = $resource->getTarget();
            $meta['type'] = $target->getResourceType()->getName();
        }

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $meta = array_merge($meta, [
                'authors' => $resourceNode->getAuthor(),
                'license' => $resourceNode->getLicense(),
            ]);
        }

        return $meta;
    }

    private function serializeDisplay(ResourceNode $resourceNode)
    {
        return [
            'fullscreen' => $resourceNode->isFullscreen(),
            'showIcon' => $resourceNode->getShowIcon(),
        ];
    }

    private function serializeRestrictions(ResourceNode $resourceNode)
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
     *
     * @param array        $data
     * @param ResourceNode $resourceNode
     * @param array        $options
     */
    public function deserialize(array $data, ResourceNode $resourceNode, array $options = [])
    {
        $this->sipe('name', 'setName', $data, $resourceNode);
        $this->sipe('slug', 'setSlug', $data, $resourceNode);

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $resourceNode);
        } else {
            $resourceNode->refreshUuid();
        }

        if (isset($data['meta']['workspace'])) {
            /** @var Workspace $workspace */
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $data['meta']['workspace']['id']]);
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

        // restrictions
        $this->sipe('restrictions.code', 'setAccessCode', $data, $resourceNode);
        $this->sipe('restrictions.allowedIps', 'setAllowedIps', $data, $resourceNode);
        $this->sipe('restrictions.hidden', 'setHidden', $data, $resourceNode);

        if (isset($data['restrictions']['dates'])) {
            $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

            $resourceNode->setAccessibleFrom($dateRange[0]);
            $resourceNode->setAccessibleUntil($dateRange[1]);
        }

        if (!in_array(OPTIONS::IGNORE_RIGHTS, $options) && isset($data['rights'])) {
            $this->deserializeRights($data['rights'], $resourceNode, $options);
        }
    }

    public function deserializeRights($rights, ResourceNode $resourceNode, array $options = [])
    {
        $existingRights = $resourceNode->getRights();

        $roles = [];
        foreach ($rights as $right) {
            if (isset($right['name'])) {
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

                $this->newRightsManager->update(
                    $resourceNode,
                    $role,
                    $this->maskManager->encodeMask($right['permissions'], $resourceNode->getResourceType()),
                    $creationPerms,
                    in_array(Options::IS_RECURSIVE, $options)
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

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/resource/resource-node.json';
    }
}
