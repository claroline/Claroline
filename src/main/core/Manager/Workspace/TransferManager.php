<?php

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Manager\File\ArchiveManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\ImportToolEvent;
use Claroline\CoreBundle\Manager\FileManager;
use Psr\Log\LoggerAwareInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;

class TransferManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ArchiveManager */
    private $archiveManager;
    /** @var FileManager */
    private $fileManager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;

    public function __construct(
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        ArchiveManager $archiveManager,
        FileManager $fileManager,
        SerializerProvider $serializer,
        Crud $crud
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->archiveManager = $archiveManager;
        $this->fileManager = $fileManager;
        $this->serializer = $serializer;
        $this->crud = $crud;
    }

    public function import(string $archivePath, ?Workspace $workspace = null): Workspace
    {
        $archive = new \ZipArchive();
        $archive->open($archivePath);

        $json = $archive->getFromName('workspace.json');
        $data = json_decode($json, true);
        // todo : put it in an event
        $data = $this->replaceResourceIds($data);

        $options = [Options::NO_MODEL];
        $fileBag = $this->archiveManager->extractFiles($archive);

        $defaultRole = $data['registration']['defaultRole'];
        unset($data['registration']['defaultRole']);

        $workspace = $this->importWorkspace($data, $workspace, $fileBag, $options);

        if ($this->crud->dispatch('create', 'pre', [$workspace, $options, $data])) {
            $this->om->persist($workspace);
            $this->om->flush();

            $roles = $this->importRoles($data, $workspace, $defaultRole);
            $this->importTools($data, $workspace, $roles, $fileBag);

            $this->crud->dispatch('create', 'post', [$workspace, $options, $data]);
        }

        return $workspace;
    }

    public function export(Workspace $workspace): string
    {
        // get data
        $fileBag = new FileBag();
        $data = $this->serialize($workspace, $fileBag);

        // create archive
        $archive = $this->archiveManager->create(null, $fileBag);
        $archivePath = $archive->filename; // we cannot read the filename once the archive is closed

        $archive->addFromString('workspace.json', json_encode($data, JSON_PRETTY_PRINT));
        $archive->close();

        return $archivePath;
    }

    /**
     * Returns a json description of the entire workspace.
     *
     * @param Workspace $workspace - the workspace to serialize
     *
     * @return array - the serialized representation of the workspace
     */
    public function serialize(Workspace $workspace, FileBag $fileBag): array
    {
        $serialized = $this->serializer->serialize($workspace, [SerializerInterface::SERIALIZE_TRANSFER]);

        if (!empty($workspace->getPoster())) {
            $fileBag->add($workspace->getUuid().'-poster', $workspace->getPoster());
        }

        if (!empty($workspace->getThumbnail())) {
            $fileBag->add($workspace->getUuid().'-thumbnail', $workspace->getThumbnail());
        }

        $serialized['roles'] = $this->exportRoles($workspace);
        $serialized['tools'] = $this->exportTools($workspace, $fileBag);

        return $serialized;
    }

    public function deserialize(array $data, Workspace $workspace, FileBag $bag, array $options = []): Workspace
    {
        // todo : put it in an event
        $data = $this->replaceResourceIds($data);

        $defaultRole = $data['registration']['defaultRole'];
        unset($data['registration']['defaultRole']);

        $this->importWorkspace($data, $workspace, $bag, $options);
        $roles = $this->importRoles($data, $workspace, $defaultRole);
        $this->importTools($data, $workspace, $roles, $bag);

        return $workspace;
    }

    private function importWorkspace(array $data, Workspace $workspace, FileBag $bag, array $options = []): Workspace
    {
        if ($workspace->getCode()) {
            unset($data['code']);
        }

        if ($workspace->getName()) {
            unset($data['name']);
        }

        $poster = $bag->get($data['id'].'-poster');
        if ($poster && !$this->fileManager->exists($poster)) {
            $file = $this->fileManager->createFile(new File($poster));
            $data['poster'] = ['url' => $file->getUrl()];
        }

        $thumbnail = $bag->get($data['id'].'-thumbnail');
        if ($thumbnail && !$this->fileManager->exists($thumbnail)) {
            $file = $this->fileManager->createFile(new File($thumbnail));
            $data['thumbnail'] = ['url' => $file->getUrl()];
        }

        /** @var Workspace $workspace */
        $workspace = $this->serializer->deserialize($data, $workspace, array_merge($options, [Options::REFRESH_UUID]));
        $this->om->persist($workspace);

        return $workspace;
    }

    private function exportRoles(Workspace $workspace): array
    {
        return array_map(function (Role $role) {
            return $this->serializer->serialize($role, [SerializerInterface::SERIALIZE_TRANSFER]);
        }, $workspace->getRoles()->toArray());
    }

    private function importRoles(array $data, Workspace $workspace, array $defaultRole): array
    {
        $roles = [];

        $this->log(sprintf('Deserializing the roles : %s', implode(', ', array_map(function ($r) { return $r['translationKey']; }, $data['roles']))));
        foreach ($data['roles'] as $roleData) {
            unset($roleData['name']);
            $roleData['workspace']['id'] = $workspace->getUuid();
            $role = new Role();

            $role->setWorkspace($workspace);
            $workspace->addRole($role);

            $this->crud->create($role, $roleData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID, Options::FORCE_FLUSH]);
            if ($defaultRole['translationKey'] === $role->getTranslationKey()) {
                $workspace->setDefaultRole($role);
            }

            $roles[$roleData['id']] = $role;
        }

        $this->om->persist($workspace);
        $this->om->flush();

        return $roles;
    }

    private function exportTools(Workspace $workspace, FileBag $fileBag): array
    {
        // we want to load the resources first
        /** @var OrderedTool[] $orderedTools */
        $orderedTools = $workspace->getOrderedTools()->toArray();

        $idx = null;
        foreach ($orderedTools as $key => $tool) {
            if ('resources' === $tool->getTool()->getName()) {
                $idx = $key;
            }
        }

        if ($idx) {
            $first = $orderedTools[$idx];
            unset($orderedTools[$idx]);
            $orderedTools = array_values($orderedTools);
            array_unshift($orderedTools, $first);
        }

        return array_map(function (OrderedTool $orderedTool) use ($fileBag) {
            // get custom tool data
            /** @var ExportToolEvent $event */
            $event = $this->dispatcher->dispatch(ToolEvents::getEventName(ToolEvents::EXPORT, AbstractTool::WORKSPACE, $orderedTool->getTool()->getName()), ExportToolEvent::class, [
                $orderedTool->getTool()->getName(), AbstractTool::WORKSPACE, $orderedTool->getWorkspace(), $fileBag,
            ]);

            return [
                'name' => $orderedTool->getTool()->getName(),
                'orderedTool' => $this->serializer->serialize($orderedTool, [SerializerInterface::SERIALIZE_TRANSFER]),
                'rights' => array_map(function (ToolRights $rights) {
                    return $this->serializer->serialize($rights, [SerializerInterface::SERIALIZE_TRANSFER]);
                }, $orderedTool->getRights()->toArray()),
                'data' => $event->getData(),
            ];
        }, $orderedTools);
    }

    private function importTools(array $data, Workspace $workspace, array $roles, FileBag $fileBag): array
    {
        $this->log('Deserializing the tools...');

        $createdObjects = $roles; // keep a map of old ID => new object for all imported objects
        foreach ($data['tools'] as $orderedToolData) {
            $tool = $this->om->getRepository(Tool::class)->findOneBy(['name' => $orderedToolData['name']]);
            if ($tool) {
                $orderedTool = $this->serializer->deserialize($orderedToolData['orderedTool'], new OrderedTool(), [SerializerInterface::REFRESH_UUID]);
                $orderedTool->setWorkspace($workspace);
                $orderedTool->setTool($tool);
                $this->om->persist($orderedTool);

                foreach ($orderedToolData['rights'] as $rightsData) {
                    if (empty($createdObjects[$rightsData['role']['id']])) {
                        continue;
                    }

                    $rights = new ToolRights();
                    $rights->setOrderedTool($orderedTool);
                    unset($rightsData['orderedToolId']);

                    $this->serializer->deserialize(array_merge($rightsData, [
                        'role' => [
                            'id' => $createdObjects[$rightsData['role']['id']]->getUuid(),
                        ],
                    ]), $rights);

                    $this->om->persist($rights);
                }

                /* @var ImportToolEvent $event */
                $event = $this->dispatcher->dispatch(
                    ToolEvents::getEventName(ToolEvents::IMPORT, AbstractTool::WORKSPACE, $orderedTool->getTool()->getName()),
                    ImportToolEvent::class,
                    [$orderedTool->getTool()->getName(), AbstractTool::WORKSPACE, $orderedTool->getWorkspace(), $fileBag, $orderedToolData['data'] ?? [], $createdObjects]
                );

                $createdObjects = array_merge([], $createdObjects, $event->getCreatedEntities());
            }
        }

        $this->om->flush();

        return $createdObjects;
    }

    private function replaceResourceIds(array $data)
    {
        if (empty($data['tools'])) {
            return $data;
        }

        $replaced = json_encode($data);

        foreach ($data['tools'] as $tool) {
            if ('resources' === $tool['name']) {
                if (empty($tool['data']) || empty($tool['data']['resources'])) {
                    break;
                }

                foreach ($tool['data']['resources'] as $resourceData) {
                    $uuid = Uuid::uuid4()->toString();

                    if (isset($resourceData['resourceNode']['id'])) {
                        $replaced = str_replace($resourceData['resourceNode']['id'], $uuid, $replaced);
                    }
                }

                break;
            }
        }

        return json_decode($replaced, true);
    }
}
