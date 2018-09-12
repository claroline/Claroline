<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Event\Resource\DecorateResourceNodeEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.resource_node")
 * @DI\Tag("claroline.serializer")
 */
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

    /** @var RightsManager */
    private $rightsManager;

    /**
     * ResourceNodeManager constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "fileSerializer"  = @DI\Inject("claroline.serializer.public_file"),
     *     "userSerializer"  = @DI\Inject("claroline.serializer.user"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager")
     * })
     *
     * @param ObjectManager        $om
     * @param StrictDispatcher     $eventDispatcher
     * @param PublicFileSerializer $fileSerializer
     * @param UserSerializer       $userSerializer
     * @param MaskManager          $maskManager
     * @param RightsManager        $rightsManager
     */
    public function __construct(
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        PublicFileSerializer $fileSerializer,
        UserSerializer $userSerializer,
        MaskManager $maskManager,
        RightsManager $rightsManager
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileSerializer = $fileSerializer;
        $this->userSerializer = $userSerializer;
        $this->rightsManager = $rightsManager;
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
            'autoId' => $resourceNode->getId(), // TODO : remove me
            'name' => $resourceNode->getName(),
            'meta' => $this->serializeMeta($resourceNode, $options),
            'permissions' => $this->rightsManager->getCurrentPermissionArray($resourceNode),
            'poster' => $this->serializePoster($resourceNode),
            'thumbnail' => $this->serializeThumbnail($resourceNode),
            // TODO : it should not be available in minimal mode
            // for now I need it to compute simple access rights (for display)
            // we should compute simple access here to avoid exposing this big object
            'rights' => $this->rightsManager->getRights($resourceNode),
        ];

        // TODO : it should not (I think) be available in minimal mode
        // for now I need it to compute rights
        if (!empty($resourceNode->getWorkspace())) {
            $serializedNode['workspace'] = [ // TODO : use workspace serializer with minimal option
                'id' => $resourceNode->getWorkspace()->getUuid(),
                'name' => $resourceNode->getWorkspace()->getName(),
                'code' => $resourceNode->getWorkspace()->getCode(),
            ];
        }

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serializedNode = array_merge($serializedNode, [
                'display' => $this->serializeDisplay($resourceNode),
                'restrictions' => $this->serializeRestrictions($resourceNode),
            ]);
        }

        $parent = $resourceNode->getParent();
        if (!empty($parent)) {
            $serializedNode['parent'] = [
                'id' => $parent->getUuid(),
                'autoId' => $parent->getId(),
                'name' => $parent->getName(),
            ];
        }

        if (in_array(Options::IS_RECURSIVE, $options)) {
            $serializedNode['children'] = array_map(function (ResourceNode $node) {
                return $this->serialize($node);
            }, $resourceNode->getChildren()->toArray());
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
        $event = $this->eventDispatcher->dispatch(
            'serialize_resource_node',
            'Resource\DecorateResourceNode',
            [
                $resourceNode,
                $unauthorizedKeys,
                $options,
            ]
        );

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
            'mimeType' => $resourceNode->getMimeType(),
            'description' => $resourceNode->getDescription(),
            'creator' => $resourceNode->getCreator() ? $this->userSerializer->serialize($resourceNode->getCreator()) : null,
            'created' => DateNormalizer::normalize($resourceNode->getCreationDate()),
            'updated' => DateNormalizer::normalize($resourceNode->getModificationDate()),
            'published' => $resourceNode->isPublished(),
            'active' => $resourceNode->isActive(),
            'views' => $resourceNode->getViewsCount(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $meta = array_merge($meta, [
                'authors' => $resourceNode->getAuthor(),
                'license' => $resourceNode->getLicense(),
                'portal' => $resourceNode->isPublishedToPortal(),
            ]);
        }

        return $meta;
    }

    private function serializeDisplay(ResourceNode $resourceNode)
    {
        return [
            'fullscreen' => $resourceNode->isFullscreen(),
            'showIcon' => $resourceNode->getShowIcon(),
            'closable' => $resourceNode->isClosable(),
            'closeTarget' => $resourceNode->getCloseTarget(),
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
     */
    public function deserialize(array $data, ResourceNode $resourceNode)
    {
        $this->sipe('name', 'setName', $data, $resourceNode);

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

        if (isset($data['rights'])) {
            $this->deserializeRights($data['rights'], $resourceNode);
        }

        $this->sipe('meta.published', 'setPublished', $data, $resourceNode);
        $this->sipe('meta.description', 'setDescription', $data, $resourceNode);
        $this->sipe('meta.portal', 'setPublishedToPortal', $data, $resourceNode);
        $this->sipe('meta.license', 'setLicense', $data, $resourceNode);
        $this->sipe('meta.authors', 'setAuthor', $data, $resourceNode);

        // display
        $this->sipe('display.fullscreen', 'setFullscreen', $data, $resourceNode);
        $this->sipe('display.showIcon', 'setShowIcon', $data, $resourceNode);
        $this->sipe('display.closable', 'setClosable', $data, $resourceNode);
        $this->sipe('display.closeTarget', 'setCloseTarget', $data, $resourceNode);

        // restrictions
        $this->sipe('restrictions.code', 'setAccessCode', $data, $resourceNode);
        $this->sipe('restrictions.allowedIps', 'setAllowedIps', $data, $resourceNode);
        $this->sipe('restrictions.hidden', 'setHidden', $data, $resourceNode);

        if (isset($data['restrictions']['dates'])) {
            $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

            $resourceNode->setAccessibleFrom($dateRange[0]);
            $resourceNode->setAccessibleUntil($dateRange[1]);
        }
    }

    private function deserializeRights($rights, ResourceNode $resourceNode)
    {
        // additional data might be required later (recursive)
        foreach ($rights as $right) {
            $creationPerms = null;
            if (isset($right['permissions']['create'])) {
                if ('directory' === $resourceNode->getResourceType()->getName()) {
                    // ugly hack to only get create rights for directories (it's the only one that can handle it).
                    $creationPerms = array_map(function (string $typeName) {
                        return $this->om
                            ->getRepository(ResourceType::class)
                            ->findOneBy(['name' => $typeName]);
                    }, $right['permissions']['create']);
                }

                unset($right['permissions']['create']);
            }

            $this->rightsManager->editPerms(
                $right['permissions'],
                $right['name'],
                $resourceNode,
                false,
                $creationPerms
            );
        }
    }
}
