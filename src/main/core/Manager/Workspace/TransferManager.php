<?php

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Crud\WorkspaceCrud;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Manager\Workspace\Transfer\OrderedToolTransfer;
use Psr\Log\LoggerAwareInterface;

class TransferManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var TempFileManager */
    private $tempFileManager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;
    /** @var OrderedToolTransfer */
    private $ots;

    public function __construct(
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        TempFileManager $tempFileManager,
        SerializerProvider $serializer,
        OrderedToolTransfer $ots,
        Crud $crud
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->tempFileManager = $tempFileManager;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->ots = $ots;
    }

    public function import(string $archivePath, ?Workspace $workspace = null): Workspace
    {
        $archive = new \ZipArchive();
        $archive->open($archivePath);

        $json = $archive->getFromName('workspace.json');
        $data = json_decode($json, true);

        $options = [WorkspaceCrud::NO_MODEL];
        $fileBag = $this->extractArchiveFiles($archive);

        $defaultRole = $data['registration']['defaultRole'];
        unset($data['registration']['defaultRole']);

        $workspace = $this->importWorkspace($data, $workspace, $options);

        if ($this->dispatch('create', 'pre', [$workspace, $options, $data])) {
            $this->om->persist($workspace);
            $this->om->flush();

            $roles = $this->importRoles($data, $workspace, $defaultRole);
            $this->importTools($data, $workspace, $roles, $fileBag);

            $this->dispatch('create', 'post', [$workspace, $options, $data]);
        }

        return $workspace;
    }

    public function export(Workspace $workspace): string
    {
        // get data
        $fileBag = new FileBag();
        $data = $this->serialize($workspace);
        $data = $this->exportFiles($data, $fileBag, $workspace);

        // create archive
        $archive = new \ZipArchive();
        $pathArch = $this->tempFileManager->generate();
        $archive->open($pathArch, \ZipArchive::CREATE);
        $archive->addFromString('workspace.json', json_encode($data, JSON_PRETTY_PRINT));

        foreach ($fileBag->all() as $archPath => $realPath) {
            $archive->addFile($realPath, $archPath);
        }

        $archive->close();

        return $pathArch;
    }

    /**
     * Returns a json description of the entire workspace.
     *
     * @param Workspace $workspace - the workspace to serialize
     *
     * @return array - the serialized representation of the workspace
     */
    public function serialize(Workspace $workspace): array
    {
        $serialized = $this->serializer->serialize($workspace);

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

        $serialized['orderedTools'] = array_map(function (OrderedTool $tool) {
            return $this->ots->serialize($tool);
        }, $orderedTools);

        return $serialized;
    }

    public function deserialize(array $data, Workspace $workspace, FileBag $bag, array $options = []): Workspace
    {
        $defaultRole = $data['registration']['defaultRole'];
        unset($data['registration']['defaultRole']);

        $this->importWorkspace($data, $workspace, $options);
        $roles = $this->importRoles($data, $workspace, $defaultRole);
        $this->importTools($data, $workspace, $roles, $bag);

        return $workspace;
    }

    //once everything is serialized, we add files to the archive.
    public function exportFiles($data, FileBag $fileBag, Workspace $workspace)
    {
        foreach ($data['orderedTools'] as $key => $orderedToolData) {
            //copied from crud
            $name = 'export_tool_'.$orderedToolData['tool'];
            //use an other even. StdClass is not pretty
            if (isset($orderedToolData['data'])) {
                /** @var ExportObjectEvent $event */
                $event = $this->dispatcher->dispatch($name, ExportObjectEvent::class, [
                    new \StdClass(), $fileBag, $orderedToolData['data'], $workspace,
                ]);
                $data['orderedTools'][$key]['data'] = $event->getData();
            }
        }

        return $data;
    }

    private function importWorkspace(array $data, Workspace $workspace, array $options = []): Workspace
    {
        if ($workspace->getCode()) {
            unset($data['code']);
        }

        if ($workspace->getName()) {
            unset($data['name']);
        }

        /** @var Workspace $workspace */
        $workspace = $this->serializer->deserialize($data, $workspace, array_merge($options, [Options::REFRESH_UUID]));
        $this->om->persist($workspace);

        return $workspace;
    }

    // this should be done by a community tool exporter
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

            // TODO : remove force flush when role creation is moved in postCopy/postCreate
            $this->crud->create($role, $roleData, [Crud::NO_PERMISSIONS, Options::FORCE_FLUSH]);
            if ($defaultRole['translationKey'] === $role->getTranslationKey()) {
                $workspace->setDefaultRole($role);
            }

            $roles[$roleData['id']] = $role;
        }

        $this->om->persist($workspace);
        $this->om->flush();

        return $roles;
    }

    private function importTools(array $data, Workspace $workspace, array $roles, FileBag $bag): array
    {
        $this->log('Pre import data update...');

        foreach ($data['orderedTools'] as $orderedToolData) {
            $this->ots->setLogger($this->logger);
            $data = $this->ots->dispatchPreEvent($data, $orderedToolData);
        }

        $this->log('Deserializing the tools...');

        $createdObjects = $roles; // keep a map of old ID => new object for all imported objects
        foreach ($data['orderedTools'] as $orderedToolData) {
            $createdObjects = array_merge([], $createdObjects, $this->ots->deserialize($orderedToolData, new OrderedTool(), $createdObjects, $workspace, $bag));
        }

        return $createdObjects;
    }

    private function extractArchiveFiles(\ZipArchive $archive): FileBag
    {
        $fileBag = new FileBag();

        $dest = $this->tempFileManager->generate();
        if (!file_exists($dest)) {
            mkdir($dest, 0777, true);
        }
        $archive->extractTo($dest);

        foreach (new \DirectoryIterator($dest) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            $location = $fileInfo->getPathname();
            $fileName = $fileInfo->getFilename();

            $fileBag->add($fileName, $location);
        }

        return $fileBag;
    }

    private function dispatch($action, $when, array $args)
    {
        return $this->crud->dispatch($action, $when, $args);
    }
}
