<?php

namespace Claroline\CoreBundle\Component\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ResourcesTool extends AbstractTool
{
    private ResourceNodeRepository $resourceRepository;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud
    ) {
        $this->resourceRepository = $om->getRepository(ResourceNode::class);
    }

    public static function getName(): string
    {
        return 'resources';
    }

    public static function getIcon(): string
    {
        return 'folder';
    }

    public function supportsContext(string $context): bool
    {
        return WorkspaceContext::getName() === $context;
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        return [
            'root' => $this->serializer->serialize(
                $this->resourceRepository->findWorkspaceRoot($contextSubject)
            ),
        ];
    }

    public function create(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): void
    {

        /*$root = $this->resourceManager->getWorkspaceRoot($workspace);
        if ($root) {
            $this->resourceManager->createRights($root, [], true, false);
        }*/

    }

    public function export(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): ?array
    {
        $root = $this->resourceRepository->findWorkspaceRoot($contextSubject);
        if (empty($root)) {
            return [];
        }

        return [
            'resources' => $this->recursiveExport($root, $fileBag),
        ];
    }

    public function import(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null, array $data = [], array $entities = []): ?array
    {
        if (empty($data['resources'])) {
            return [];
        }

        $resources = $data['resources'];

        // we need to push the resource types with linked resources last, because we need all resources to be created
        // to link them to the new resources.
        // this should not be done here and as is it doesn't work in all cases (e.g. if we link paths to others paths).
        $typesWithResourceLinks = ['innova_path', 'shortcut'];
        uksort($resources, function (int $a, int $b) use ($resources, $typesWithResourceLinks) {
            if (in_array($resources[$a]['resourceNode']['meta']['type'], $typesWithResourceLinks)) {
                return 1;
            } elseif (in_array($resources[$b]['resourceNode']['meta']['type'], $typesWithResourceLinks)) {
                return -1;
            }

            // we want to keep the original order (required for the parent directories to be created first)
            // that's why we use uksort and not usort (from the usort doc : If two members compare as equal, they retain their original order. Prior to PHP 8.0.0, their relative order in the sorted array was undefined.)
            return $a - $b;
        });

        /** @var Workspace $workspace */
        $workspace = $contextSubject;

        // manage workspace opening
        // this is for retro-compatibility, we have stored the autoincrement id of the resource in the workspace options
        // when using the UUID, replacement is automatically done in the serialized data
        $workspaceOptions = $workspace->getOptions()->getDetails();
        $openingResourceId = null;
        if ($workspaceOptions && 'resource' === $workspaceOptions['opening_target'] && !empty($workspaceOptions['workspace_opening_resource'])) {
            // this only works because the WorkspaceSerializer::deserialize does not check if the resource exists
            $openingResourceId = $workspaceOptions['workspace_opening_resource'];
        }

        foreach ($resources as $resourceData) {
            // create resource node
            $nodeData = $resourceData['resourceNode'];
            unset($nodeData['workspace']);
            unset($nodeData['slug']);

            $resourceNode = new ResourceNode();
            $resourceNode->setWorkspace($workspace);

            // workspace name root directory based on the new workspace name
            if (empty($nodeData['parent'])) {
                $nodeData['name'] = $workspace->getName();
                $nodeData['code'] = $workspace->getCode();
            }

            if (!empty($nodeData['parent']) && $entities[$nodeData['parent']['id']]) {
                $resourceNode->setParent($entities[$nodeData['parent']['id']]);
                unset($nodeData['parent']);
            }

            $this->crud->create($resourceNode, $nodeData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::NO_RIGHTS/* , Options::REFRESH_UUID */]);

            $entities[$nodeData['id']] = $resourceNode;

            // create rights
            if (!empty($resourceData['rights'])) {
                foreach ($resourceData['rights'] as $rightsData) {
                    if (empty($entities[$rightsData['role']['id']])) {
                        continue;
                    }

                    $role = $entities[$rightsData['role']['id']];

                    $rights = new ResourceRights();
                    $rights->setResourceNode($resourceNode);
                    $this->serializer->deserialize(array_merge($rightsData, [
                        'role' => [
                            'id' => $role->getUuid(),
                        ],
                    ]), $rights);

                    $this->om->persist($rights);
                }
            }

            // create custom resource Entity
            $resourceClass = $resourceNode->getResourceType()->getClass();

            // should be removed. It's only used by quizzes
            $resSerializer = $this->serializer->get($resourceClass);
            $resSerializeOptions = method_exists($resSerializer, 'getCopyOptions') ? $resSerializer->getCopyOptions() : [];

            /** @var AbstractResource $resource */
            $resource = new $resourceClass();
            $resource->setResourceNode($resourceNode);

            $this->crud->create($resource, $resourceData['resource'], array_merge([Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID], $resSerializeOptions));

            $importEvent = new ImportResourceEvent($resource, $fileBag, $resourceData);
            $this->dispatcher->dispatch($importEvent, 'resource.'.$resourceNode->getType().'.import');

            // replace workspace opening resource id by the new one
            // this is for retro-compatibility, when we have stored the autoincrement id of the resource in the workspace options
            // when using the UUID, replacement is automatically done in the serialized data
            if (!empty($openingResourceId) && $resourceData['autoId'] === $openingResourceId) {
                $workspace->getOptions()->setDetails(array_merge($workspaceOptions, [
                    'workspace_opening_resource' => $resourceNode->getUuid(),
                ]));
            }

            // we need the resources to be persisted in DB to be exploitable in listeners (e.g. path do a DB call to retrieve linked resources)
            $this->om->forceFlush();
        }

        return $entities;
    }

    private function recursiveExport(ResourceNode $resourceNode, FileBag $fileBag): array
    {
        $exported = [];

        $resource = $this->om->getRepository($resourceNode->getClass())->findOneBy(['resourceNode' => $resourceNode]);
        if ($resource) {
            // should be removed. It's only used by quizzes
            $resSerializer = $this->serializer->get($resourceNode->getClass());
            $resSerializeOptions = method_exists($resSerializer, 'getCopyOptions') ? $resSerializer->getCopyOptions() : [];

            $exportEvent = new ExportResourceEvent($resource, $fileBag);
            $this->dispatcher->dispatch($exportEvent, 'resource.'.$resourceNode->getType().'.export');

            $exported[] = array_merge([
                'resourceNode' => $this->serializer->serialize($resourceNode, [SerializerInterface::SERIALIZE_TRANSFER, Options::NO_RIGHTS]),
                'resource' => $this->serializer->serialize($resource, array_merge([SerializerInterface::SERIALIZE_TRANSFER], $resSerializeOptions)),
                'rights' => array_map(function (ResourceRights $rights) {
                    return $this->serializer->serialize($rights, [SerializerInterface::SERIALIZE_TRANSFER]);
                }, $resourceNode->getRights()->toArray()),
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
