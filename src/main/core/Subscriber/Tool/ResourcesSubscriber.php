<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Subscriber\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\ImportToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourcesSubscriber implements EventSubscriberInterface
{
    const NAME = 'resources';

    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ResourceNodeRepository */
    private $resourceRepository;
    /** @var Crud */
    private $crud;

    public function __construct(
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        SerializerProvider $serializer,
        Crud $crud
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->serializer = $serializer;
        $this->crud = $crud;

        $this->resourceRepository = $om->getRepository(ResourceNode::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::DESKTOP, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::WORKSPACE, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::EXPORT, AbstractTool::WORKSPACE, static::NAME) => 'onExport',
            ToolEvents::getEventName(ToolEvents::IMPORT, AbstractTool::WORKSPACE, static::NAME) => 'onImport',
        ];
    }

    public function onOpen(OpenToolEvent $event)
    {
        $root = null;
        if (AbstractTool::WORKSPACE === $event->getContext()) {
            // filters resources for the current workspace
            $root = $this->serializer->serialize(
                $this->resourceRepository->findWorkspaceRoot($event->getWorkspace())
            );
        }

        $event->setData([
            'root' => $root,
        ]);

        $event->stopPropagation();
    }

    public function onExport(ExportToolEvent $event)
    {
        $root = $this->resourceRepository->findWorkspaceRoot($event->getWorkspace());
        if (empty($root)) {
            return;
        }

        $event->setData([
            'resources' => $this->recursiveExport($root, $event->getFileBag()),
        ]);
    }

    public function onImport(ImportToolEvent $event)
    {
        $data = $event->getData();
        if (empty($data['resources'])) {
            return;
        }

        foreach ($data['resources'] as $resourceData) {
            // create resource node
            $nodeData = $resourceData['resourceNode'];
            unset($nodeData['workspace']);

            $resourceNode = new ResourceNode();
            $resourceNode->setWorkspace($event->getWorkspace());
            if (!empty($nodeData['parent']) && $event->getCreatedEntity($nodeData['parent']['id'])) {
                $resourceNode->setParent($event->getCreatedEntity($nodeData['parent']['id']));
            }

            $this->crud->create($resourceNode, $nodeData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

            $event->addCreatedEntity($nodeData['id'], $resourceNode);

            // create custom resource Entity
            $resourceClass = $resourceNode->getResourceType()->getClass();

            /** @var AbstractResource $resource */
            $resource = $this->crud->create($resourceClass, $resourceData['resource'], [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);
            $resource->setResourceNode($resourceNode);

            $this->dispatcher->dispatch(
                'resource.'.$resourceNode->getType().'.import',
                ImportResourceEvent::class,
                [$resource, $event->getFileBag(), $resourceData]
            );
        }

        // rename root directory based on the new workspace name
        $root = $this->resourceRepository->findWorkspaceRoot($event->getWorkspace());
        if ($root) {
            $root->setName($event->getWorkspace()->getName());
            $this->om->persist($root);
            $this->om->flush();
        }
    }

    private function recursiveExport(ResourceNode $resourceNode, FileBag $fileBag)
    {
        $exported = [];

        $resource = $this->om->getRepository($resourceNode->getClass())->findOneBy(['resourceNode' => $resourceNode]);
        if ($resource) {
            // should be removed. It's only used by quizzes
            $resSerializer = $this->serializer->get($resourceNode->getClass());
            $resSerializeOptions = method_exists($resSerializer, 'getCopyOptions') ? $resSerializer->getCopyOptions() : [];

            /** @var ExportResourceEvent $exportEvent */
            $exportEvent = $this->dispatcher->dispatch(
                'resource.'.$resourceNode->getType().'.export',
                ExportResourceEvent::class,
                [$resource, $fileBag]
            );

            $exported[] = array_merge([
                'resourceNode' => $this->serializer->serialize($resourceNode),
                'resource' => $this->serializer->serialize($resource, $resSerializeOptions),
            ], $exportEvent->getData());

            foreach ($resourceNode->getChildren() as $child) {
                if ($child->isActive()) {
                    $exported = array_merge($exported, $this->recursiveExport($child, $fileBag));
                }
            }
        }

        return $exported;
    }
}
