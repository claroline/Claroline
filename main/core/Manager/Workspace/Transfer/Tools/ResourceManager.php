<?php

namespace Claroline\CoreBundle\Manager\Workspace\Transfer\Tools;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Manager\ResourceManager as ResManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @DI\Service("claroline.transfer.resource_manager")
 */
class ResourceManager implements ToolImporterInterface
{
    use LoggableTrait;

    /**
     * WorkspaceSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer"       = @DI\Inject("claroline.api.serializer"),
     *     "finder"           = @DI\Inject("claroline.api.finder"),
     *     "crud"             = @DI\Inject("claroline.api.crud"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher"  = @DI\Inject("claroline.event.event_dispatcher"),
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager")
     * })
     *

     * @param SerializerProvider $serializer
     */
    public function __construct(
        SerializerProvider $serializer,
        UserManager $userManager,
        FinderProvider $finder,
        Crud $crud,
        TokenStorage $tokenStorage,
        ResManager $resourceManager,
        ObjectManager $om,
        StrictDispatcher $eventDispatcher
      ) {
        $this->serializer = $serializer;
        $this->om = $om;
        $this->finder = $finder;
        $this->crud = $crud;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->dispatcher = $eventDispatcher;
        $this->resourceManager = $resourceManager;
    }

    /**
     * @return array
     */
    public function serialize(Workspace $workspace, array $options): array
    {
        $root = $this->om->getRepository(ResourceNode::class)
          ->findOneBy(['parent' => null, 'workspace' => $workspace->getId()]);

        return $this->recursiveSerialize($root, $options);
    }

    private function recursiveSerialize(ResourceNode $root, array $options, array $data = ['nodes' => [], 'resources' => []])
    {
        $node = $this->serializer->serialize($root, array_merge($options, [Options::SERIALIZE_MINIMAL]));
        $resSerializer = $this->serializer->get($root->getClass());
        $resSerializeOptions = method_exists($resSerializer, 'getCopyOptions') ? $resSerializer->getCopyOptions()['serialize'] : [];
        $res = $this->om->getRepository($root->getClass())->findOneBy(['resourceNode' => $root]);

        if ($res) {
            $resource = array_merge(
                $this->serializer->serialize($res, $resSerializeOptions),
                ['_nodeId' => $root->getUuid(), '_class' => $node['meta']['className'], '_type' => $node['meta']['type']]
            );

            $data['nodes'][] = $node;
            $data['resources'][] = $resource;

            foreach ($root->getChildren() as $child) {
                $data = $this->recursiveSerialize($child, $options, $data);
            }
        }

        return $data;
    }

    public function prepareImport(array $orderedToolData, array $data): array
    {
        foreach ($orderedToolData['data']['resources'] as $serialized) {
            $event = $this->dispatcher->dispatch(
                'transfer.'.$serialized['_type'].'.import.before',
                ImportObjectEvent::class,
                [null, $serialized, null, $data]
            );

            $data = $event->getExtra();
        }

        return $data;
    }

    public function deserialize(array $data, Workspace $workspace, array $options, FileBag $bag)
    {
        $created = $this->deserializeNodes($data['nodes'], $workspace);
        $this->deserializeResources($data['resources'], $workspace, $created, $bag);

        $root = $this->resourceManager->getWorkspaceRoot($workspace);

        if ($root) {
            $root->setName($workspace->getName());
            $this->om->persist($root);
        }

        $this->om->flush();
    }

    private function deserializeNodes(array $nodes, Workspace $workspace)
    {
        $created = [];

        foreach ($nodes as $data) {
            $rights = $data['rights'];
            unset($data['rights']);
            $node = $this->om->getObject($data, ResourceNode::class) ?? new ResourceNode();
            $node = $this->serializer->deserialize($data, $node);
            $node->setWorkspace($workspace);
            $this->serializer->get(ResourceNode::class)->deserialize(['rights' => $rights], $node);

            if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
                $node->setCreator($this->tokenStorage->getToken()->getUser());
            } else {
                $creator = $this->userManager->getDefaultClarolineAdmin();
                $node->setCreator($creator);
            }

            $created[$node->getUuid()] = $node;
            if (isset($data['parent'])) {
                $node->setParent($created[$data['parent']['id']]);
            }

            $this->om->persist($node);
        }

        return $created;
    }

    private function deserializeResources(array $resources, Workspace $workspace, array $nodes, FileBag $bag)
    {
        $this->om->startFlushSuite();

        foreach ($resources as $data) {
            $resource = new $data['_class']();
            $resource->setResourceNode($nodes[$data['_nodeId']]);
            $this->dispatchCrud('create', 'pre', [$resource, [Options::WORKSPACE_COPY]]);
            $this->serializer->deserialize($data, $resource, [Options::REFRESH_UUID]);
            $this->dispatchCrud('create', 'post', [$resource, [Options::WORKSPACE_COPY]]);
            $this->dispatcher->dispatch(
                'transfer.'.$data['_type'].'.import.after',
                ImportObjectEvent::class,
                [$bag, $data, $resource, null, $workspace]
            );
            $this->om->persist($resource);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @DI\Observe("export_tool_resource_manager")
     */
    public function onExport(ExportObjectEvent $event)
    {
        $data = $event->getData();

        foreach ($data['resources'] as $key => $serialized) {
            $node = $this->om->getRepository(ResourceNode::class)->findOneByUuid($serialized['_nodeId']);
            $resource = $this->om->getRepository($serialized['_class'])->findOneBy(['resourceNode' => $node]);

            /** @var ExportObjectEvent $new */
            $new = $this->dispatcher->dispatch(
                'transfer.'.$node->getResourceType()->getName().'.export',
                ExportObjectEvent::class,
                [$resource, $event->getFileBag(), $serialized, $event->getWorkspace()]
            );

            $event->overwrite('resources.'.$key, $new->getData());
        }
    }

    /**
     * @DI\Observe("import_tool_resource_manager")
     */
    public function onImport(ImportObjectEvent $event)
    {
        $this->log('Importing resource files...');
        $data = $event->getData();

        foreach ($data['resources'] as $serialized) {
            $this->dispatcher->dispatch(
                'transfer.'.$serialized['_type'].'.import',
                ImportObjectEvent::class,
                [$event->getFileBag(), $serialized]
            );
        }
    }

    /**
     * We dispatch 2 events: a generic one and an other with a custom name.
     * Listen to what you want. Both have their uses.
     *
     * @param string $action (create, copy, delete, patch, update)
     * @param string $when   (post, pre)
     * @param array  $args
     *
     * @return bool
     *
     * Same dispatcher than the crud one
     */
    public function dispatchCrud($action, $when, array $args)
    {
        $name = 'crud_'.$when.'_'.$action.'_object';
        $eventClass = ucfirst($action);
        $generic = $this->dispatcher->dispatch($name, 'Claroline\\AppBundle\\Event\\Crud\\'.$eventClass.'Event', $args);
        $className = $this->om->getMetadataFactory()->getMetadataFor(get_class($args[0]))->getName();
        $serializedName = $name.'_'.strtolower(str_replace('\\', '_', $className));
        $specific = $this->dispatcher->dispatch($serializedName, 'Claroline\\AppBundle\\Event\\Crud\\'.$eventClass.'Event', $args);

        return $generic->isAllowed() && $specific->isAllowed();
    }
}
