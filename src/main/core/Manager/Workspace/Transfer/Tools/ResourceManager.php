<?php

namespace Claroline\CoreBundle\Manager\Workspace\Transfer\Tools;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Manager\ResourceManager as ResManager;
use Psr\Log\LoggerAwareInterface;

class ResourceManager implements ToolImporterInterface, LoggerAwareInterface
{
    use LoggableTrait;

    /** @var SerializerProvider */
    private $serializer;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ResManager */
    private $resourceManager;

    public function __construct(
        SerializerProvider $serializer,
        Crud $crud,
        ResManager $resourceManager,
        ObjectManager $om,
        StrictDispatcher $eventDispatcher
      ) {
        $this->serializer = $serializer;
        $this->om = $om;
        $this->crud = $crud;
        $this->dispatcher = $eventDispatcher;
        $this->resourceManager = $resourceManager;
    }

    public function serialize(Workspace $workspace, array $options): array
    {
        /** @var ResourceNode $root */
        $root = $this->om->getRepository(ResourceNode::class)
          ->findOneBy(['parent' => null, 'workspace' => $workspace->getId()]);

        return $this->recursiveSerialize($root, $options);
    }

    private function recursiveSerialize(ResourceNode $root, array $options, array $data = ['nodes' => [], 'resources' => []])
    {
        $node = $this->serializer->serialize($root, $options);
        $resSerializer = $this->serializer->get($root->getClass());
        $resSerializeOptions = method_exists($resSerializer, 'getCopyOptions') ? $resSerializer->getCopyOptions() : [];
        $res = $this->om->getRepository($root->getClass())->findOneBy(['resourceNode' => $root]);

        if ($res) {
            $resource = array_merge($this->serializer->serialize($res, $resSerializeOptions), [
                '_nodeId' => $root->getUuid(),
                '_class' => $node['meta']['className'],
                '_type' => $node['meta']['type'],
            ]);

            $data['nodes'][] = $node;
            $data['resources'][] = $resource;

            foreach ($root->getChildren() as $child) {
                if ($child->isActive()) {
                    $data = $this->recursiveSerialize($child, $options, $data);
                }
            }
        }

        return $data;
    }

    public function prepareImport(array $orderedToolData, array $data): array
    {
        foreach ($orderedToolData['data']['resources'] as $serialized) {
            /** @var ImportObjectEvent $event */
            $event = $this->dispatcher->dispatch(
                'transfer.'.$serialized['_type'].'.import.before',
                ImportObjectEvent::class,
                [null, $serialized, null, $data]
            );

            $data = $event->getExtra();
        }

        return $data;
    }

    public function deserialize(array $data, Workspace $workspace, array $options, array $newEntities, FileBag $bag): array
    {
        $createdNodes = $this->deserializeNodes($data['nodes'], $workspace);
        $createdResources = $this->deserializeResources($data['resources'], $workspace, $createdNodes, $bag);

        $root = $this->resourceManager->getWorkspaceRoot($workspace);

        if ($root) {
            $root->setName($workspace->getName());
            $this->om->persist($root);
        }

        $this->om->flush();

        // Exporting AbstractResources may not be required
        return array_merge($createdNodes, $createdResources);
    }

    private function deserializeNodes(array $nodes, Workspace $workspace): array
    {
        $created = [];

        $options = [Options::REFRESH_UUID];

        foreach ($nodes as $data) {
            unset($data['workspace']);

            $node = new ResourceNode();
            $node->setWorkspace($workspace);

            $created[$data['id']] = $node;

            $node = $this->serializer->deserialize($data, $node, $options);
            if (isset($data['parent'])) {
                $node->setParent($created[$data['parent']['id']]);
            }

            if ($this->dispatchCrud('create', 'pre', [$node, $options, $data])) {
                $this->om->persist($node);

                $this->dispatchCrud('create', 'post', [$node, $options, $data]);
            }
        }

        return $created;
    }

    private function deserializeResources(array $resources, Workspace $workspace, array $nodes, FileBag $bag): array
    {
        $this->om->startFlushSuite();

        $options = [Options::REFRESH_UUID];

        $createdResources = [];
        foreach ($resources as $data) {
            /** @var AbstractResource $resource */
            $resource = new $data['_class']();
            $resource->setResourceNode($nodes[$data['_nodeId']]);

            $this->dispatchCrud('create', 'pre', [$resource, $options, $data]);
            $this->serializer->deserialize($data, $resource, $options);
            $this->dispatchCrud('create', 'post', [$resource, $options, $data]);
            $this->dispatcher->dispatch(
                'transfer.'.$data['_type'].'.import.after',
                ImportObjectEvent::class,
                [$bag, $data, $resource, null, $workspace]
            );
            $this->om->persist($resource);

            $createdResources[$data['id']] = $resource;
        }

        $this->om->endFlushSuite();

        return $createdResources;
    }

    public function onExport(ExportObjectEvent $event)
    {
        $data = $event->getData();

        foreach ($data['resources'] as $key => $serialized) {
            $node = $this->om->getRepository(ResourceNode::class)->findOneBy(['uuid' => $serialized['_nodeId']]);
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
     * @todo must directly use crud actions, not only the events
     */
    private function dispatchCrud($action, $when, array $args)
    {
        return $this->crud->dispatch($action, $when, $args);
    }
}
