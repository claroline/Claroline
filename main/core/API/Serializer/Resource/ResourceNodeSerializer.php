<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Event\Resource\DecorateResourceNodeEvent;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\BreadcrumbManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\Resource\ResourceMenuManager;
use Claroline\CoreBundle\Manager\RightsManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @todo : grab deserialization from Claroline\CoreBundle\Manager\Resource\ResourceNodeManager
 *
 * @DI\Service("claroline.serializer.resource_node")
 * @DI\Tag("claroline.serializer")
 */
class ResourceNodeSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var PublicFileSerializer */
    private $fileSerializer;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var MaskManager */
    private $maskManager;

    /** @var BreadcrumbManager */
    private $breadcrumbManager;

    /** @var ResourceMenuManager */
    private $menuManager;

    /** @var RightsManager */
    private $rightsManager;

    /**
     * ResourceNodeManager constructor.
     *
     * @DI\InjectParams({
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "authorization"     = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher"   = @DI\Inject("claroline.event.event_dispatcher"),
     *     "fileSerializer"    = @DI\Inject("claroline.serializer.public_file"),
     *     "userSerializer"    = @DI\Inject("claroline.serializer.user"),
     *     "maskManager"       = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager"     = @DI\Inject("claroline.manager.rights_manager"),
     *     "breadcrumbManager" = @DI\Inject("claroline.manager.breadcrumb_manager"),
     *     "menuManager"       = @DI\Inject("claroline.manager.resource_menu_manager")
     * })
     *
     * @param ObjectManager                 $om
     * @param AuthorizationCheckerInterface $authorization
     * @param StrictDispatcher              $eventDispatcher
     * @param PublicFileSerializer          $fileSerializer
     * @param UserSerializer                $userSerializer
     * @param MaskManager                   $maskManager
     * @param BreadcrumbManager             $breadcrumbManager
     * @param ResourceMenuManager           $menuManager
     * @param RightsManager                 $rightsManager
     */
    public function __construct(
        ObjectManager $om,
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $eventDispatcher,
        PublicFileSerializer $fileSerializer,
        UserSerializer $userSerializer,
        MaskManager $maskManager,
        BreadcrumbManager $breadcrumbManager,
        ResourceMenuManager $menuManager,
        RightsManager $rightsManager
    ) {
        $this->om = $om;
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileSerializer = $fileSerializer;
        $this->userSerializer = $userSerializer;
        $this->maskManager = $maskManager;
        $this->breadcrumbManager = $breadcrumbManager;
        $this->rightsManager = $rightsManager;
        $this->menuManager = $menuManager;
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
        $serializedNode = [
            'id' => $resourceNode->getGuid(),
            'autoId' => $resourceNode->getId(),
            'actualId' => $resourceNode->getId(),
            'name' => $resourceNode->getName(),
            'thumbnail' => $resourceNode->getThumbnail() ? '/'.$resourceNode->getThumbnail()->getRelativeUrl() : null, // todo : add as ResourceNode prop
            'poster' => $this->serializePoster($resourceNode),
            'meta' => $this->serializeMeta($resourceNode),
            'display' => $this->serializeDisplay($resourceNode),
            'restrictions' => $this->getRestrictions($resourceNode),
            'rights' => [
                'current' => $this->getCurrentPermissions($resourceNode),
            ],
            'shortcuts' => $this->getShortcuts($resourceNode),
            'breadcrumb' => $this->breadcrumbManager->getBreadcrumb($resourceNode),
        ];

        if (!empty($resourceNode->getWorkspace())) {
            $serializedNode['workspace'] = [
                'id' => $resourceNode->getWorkspace()->getGuid(),
                'name' => $resourceNode->getWorkspace()->getName(),
                'code' => $resourceNode->getWorkspace()->getCode(),
            ];
        }

        if ($this->hasPermission('ADMINISTRATE', $resourceNode)) {
            $serializedNode['rights']['all'] = $this->getRights($resourceNode);
        }

        return $this->decorate($resourceNode, $serializedNode);
    }

    /**
     * Dispatches an event to let plugins add some custom data to the serialized node.
     * For example, SocialMedia adds the number of likes.
     *
     * @param ResourceNode $resourceNode   - the original node entity
     * @param array        $serializedNode - the serialized version of the node
     *
     * @return array - the decorated node
     */
    private function decorate(ResourceNode $resourceNode, array $serializedNode)
    {
        $unauthorizedKeys = array_keys($serializedNode);

        // 'poster' is a key that can be overridden by another plugin. For example: UrlBundle
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
            ]
        );

        return array_merge(
            $serializedNode,
            $event->getInjectedData()
        );
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
                ->getRepository('Claroline\CoreBundle\Entity\File\PublicFile')
                ->findOneBy(['url' => $resourceNode->getPoster()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    private function serializeMeta(ResourceNode $resourceNode)
    {
        return [
            'type' => $resourceNode->getResourceType()->getName(),
            'mimeType' => $resourceNode->getMimeType(),
            'description' => $resourceNode->getDescription(),
            'created' => $resourceNode->getCreationDate()->format('Y-m-d\TH:i:s'),
            'updated' => $resourceNode->getModificationDate()->format('Y-m-d\TH:i:s'),
            'license' => $resourceNode->getLicense(),
            'authors' => $resourceNode->getAuthor(),
            'published' => $resourceNode->isPublished(),
            'portal' => $resourceNode->isPublishedToPortal(),
            'isManager' => $this->rightsManager->isManager($resourceNode), // todo : data about current user should not be here (should be in `rights` section)
            'creator' => $resourceNode->getCreator() ? $this->userSerializer->serialize($resourceNode->getCreator()) : null,
            'actions' => $this->getActions($resourceNode),
            'views' => $resourceNode->getViewsCount(),
            'icon' => $resourceNode->getIcon() ? '/'.$resourceNode->getIcon()->getRelativeUrl() : null,
            'parent' => $resourceNode->getParent() ? [
                'id' => $resourceNode->getParent()->getGuid(),
                'name' => $resourceNode->getParent()->getName(),
            ] : null,
        ];
    }

    private function getCurrentPermissions($resourceNode)
    {
        return $this->rightsManager->getCurrentPermissionArray($resourceNode);
    }

    private function serializeDisplay(ResourceNode $resourceNode)
    {
        return [
            'fullscreen' => $resourceNode->isFullscreen(),
            'closable' => $resourceNode->isClosable(),
            'closeTarget' => $resourceNode->getCloseTarget(),
        ];
    }

    private function getRestrictions(ResourceNode $resourceNode)
    {
        return [
            'dates' => DateRangeNormalizer::normalize(
                $resourceNode->getAccessibleFrom(),
                $resourceNode->getAccessibleUntil()
            ),
            'code' => $resourceNode->getAccessCode(),
            'allowedIps' => $resourceNode->getAllowedIps(),
        ];
    }

    private function getActions(ResourceNode $resourceNode)
    {
        //ResourceManager::isResourceActionImplemented(ResourceType $resourceType = null, $actionName)
        $actions = $this->menuManager->getMenus($resourceNode);
        $data = [];
        $currentPerms = $this->getCurrentPermissions($resourceNode); // todo : avoid duplicate by reusing the result of l115
        $currentMask = $this->maskManager->encodeMask($currentPerms, $resourceNode->getResourceType());

        foreach ($actions as $action) {
            $data[$action->getName()] = [
                'name' => $action->getName(),
                'mask' => $action->getValue(),
                'group' => $action->getGroup(),
                'async' => $action->isAsync(),
                'custom' => $action->isCustom(),
                'form' => $action->isForm(),
                'icon' => $action->getIcon(),
            ];
        }

        return array_filter($data, function ($action) use ($currentMask) {
            return $action['mask'] & $currentMask;
        });
    }

    private function getRights(ResourceNode $resourceNode)
    {
        $decoders = $resourceNode->getResourceType()->getMaskDecoders()->toArray();
        $serializedDecoders = array_map(function (MaskDecoder $decoder) {
            return [
                'name' => $decoder->getName(),
                'value' => $decoder->getValue(),
            ];
        }, $decoders);

        $serializedRights = [];
        $rights = $resourceNode->getRights();
        foreach ($rights as $right) {
            $serializedRights[$right->getRole()->getName()] = [
                'id' => $right->getId(),
                'mask' => $right->getMask(),
                'role' => [
                    'id' => $right->getRole()->getId(),
                    'name' => $right->getRole()->getName(),
                    'key' => $right->getRole()->getTranslationKey(),
                ],
                'permissions' => array_merge(
                    $this->maskManager->decodeMask($right->getMask(), $resourceNode->getResourceType()),
                    // todo : array_keys should be remove when `getCreatableTypes` will return only types without translations
                    ['create' => array_keys($this->rightsManager->getCreatableTypes([$right->getRole()->getName()], $resourceNode))]
                ),
            ];
        }

        return [
            'decoders' => $serializedDecoders,
            'permissions' => $serializedRights,
        ];
    }

    private function getShortcuts(ResourceNode $resourceNode)
    {
        $shortcuts = $resourceNode->getShortcuts()->toArray();

        return array_map(function (ResourceShortcut $shortcut) {
            $node = $shortcut->getResourceNode();

            return [
                'name' => $node->getName(),
                'workspace' => [
                    'id' => $node->getWorkspace()->getId(),
                    'name' => $node->getWorkspace()->getName(),
                    'code' => $node->getWorkspace()->getCode(),
                ],
            ];
        }, $shortcuts);
    }

    private function hasPermission($permission, ResourceNode $resourceNode)
    {
        $collection = new ResourceCollection([$resourceNode]);

        return $this->authorization->isGranted($permission, $collection);
    }
}
