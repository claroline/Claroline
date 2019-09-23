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
            //also used for the export. It's not pretty.
            'autoId' => $resourceNode->getId(),
            'id' => $resourceNode->getUuid(),
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
            'rights' => $this->rightsManager->getRights($resourceNode, $options),
        ];

        // TODO : it should not (I think) be available in minimal mode
        // for now I need it to compute rights
        if ($resourceNode->getWorkspace() && !in_array(Options::REFRESH_UUID, $options)) {
            $serializedNode['workspace'] = [ // TODO : use workspace serializer with minimal option
                'id' => $resourceNode->getWorkspace()->getUuid(),
                'slug' => $resourceNode->getWorkspace()->getSlug(),
                'autoId' => $resourceNode->getWorkspace()->getId(), // TODO : remove me
                'name' => $resourceNode->getWorkspace()->getName(),
                'code' => $resourceNode->getWorkspace()->getCode(),
            ];
        }

        $parent = $resourceNode->getParent();
        if (!empty($parent)) {
            $serializedNode['parent'] = [
                'id' => $parent->getUuid(),
                'autoId' => $parent->getId(), // TODO : remove me
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
    public function deserialize(array $data, ResourceNode $resourceNode, array $options = [])
    {
        $this->sipe('name', 'setName', $data, $resourceNode);

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $resourceNode);
        }

        if (isset($data['meta']['workspace'])) {
            /** @var Workspace $workspace */
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $data['meta']['workspace']['uuid']]);
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

        if (isset($data['rights'])) {
            $this->deserializeRights($data['rights'], $resourceNode, $options);
        }

        $this->sipe('meta.published', 'setPublished', $data, $resourceNode);
        $this->sipe('meta.description', 'setDescription', $data, $resourceNode);
        $this->sipe('meta.license', 'setLicense', $data, $resourceNode);
        $this->sipe('meta.authors', 'setAuthor', $data, $resourceNode);
        $this->sipe('meta.commentsActivated', 'setCommentsActivated', $data, $resourceNode);

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

    private function deserializeRights($rights, ResourceNode $resourceNode, array $options = [])
    {
        // additional data might be required later (recursive)
        foreach ($rights as $right) {
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

            $recursive = in_array(Options::IS_RECURSIVE, $options) ? true : false;

            if (isset($right['name'])) {
                $role = $this->om->getRepository(Role::class)->findOneBy(['name' => $right['name']]);
            } else {
                $role = $this->om->getRepository(Role::class)->findOneBy(
                  [
                    'translationKey' => $right['translationKey'],
                    'workspace' => $resourceNode->getWorkspace()->getId(),
                  ]
                );
            }

            if ($role) {
                //if we update (we need the id anyway)
                if ($resourceNode->getId()) {
                    $this->newRightsManager->update(
                      $resourceNode,
                      $role,
                      $this->maskManager->encodeMask($right['permissions'], $resourceNode->getResourceType()),
                      $creationPerms,
                      $recursive
                  );
                //otherwise the old one will do the trick
                } else {
                    $this->rightsManager->editPerms(
                      $right['permissions'],
                      $role->getName(),
                      $resourceNode,
                      false,
                      $creationPerms
                  );
                }
            } else {
                //role not found ... how to retrieve it ?
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
