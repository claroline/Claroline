<?php

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Tool\ToolProvider;
use Claroline\AppBundle\Manager\File\ArchiveManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\FileManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;

class TransferManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly ArchiveManager $archiveManager,
        private readonly FileManager $fileManager,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly ToolProvider $toolProvider
    ) {
    }

    public function import(string $archivePath, Workspace $workspace = null): Workspace
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

        $archive->close();

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

        if (!empty($workspace->getPoster()) && $this->fileManager->exists($workspace->getPoster())) {
            $fileBag->add($workspace->getUuid().'-poster', $workspace->getPoster());
        }

        if (!empty($workspace->getThumbnail()) && $this->fileManager->exists($workspace->getThumbnail())) {
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

        $defaultRole = null;
        if (!empty($data['registration']) && !empty($data['registration']['defaultRole'])) {
            $defaultRole = $data['registration']['defaultRole'];
            unset($data['registration']['defaultRole']);
        }

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
            $data['poster'] = $file->getUrl();
        }

        $thumbnail = $bag->get($data['id'].'-thumbnail');
        if ($thumbnail && !$this->fileManager->exists($thumbnail)) {
            $file = $this->fileManager->createFile(new File($thumbnail));
            $data['thumbnail'] = $file->getUrl();
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

    private function importRoles(array $data, Workspace $workspace, array $defaultRole = null): array
    {
        $roles = [];

        $this->logger->debug(sprintf('Deserializing the roles : %s', implode(', ', array_map(function ($r) { return $r['translationKey']; }, $data['roles']))));
        foreach ($data['roles'] as $roleData) {
            unset($roleData['name']);
            $roleData['workspace']['id'] = $workspace->getUuid();
            $role = new Role();

            $role->setWorkspace($workspace);
            $workspace->addRole($role);

            $this->crud->create($role, $roleData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID, Options::FORCE_FLUSH]);
            if (!empty($defaultRole) && $defaultRole['translationKey'] === $role->getTranslationKey()) {
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
        $orderedTools = $this->toolProvider->getEnabledTools(WorkspaceContext::getName(), $workspace);

        $idx = null;
        foreach ($orderedTools as $key => $tool) {
            if ('resources' === $tool->getName()) {
                $idx = $key;
            }
        }

        if ($idx) {
            $first = $orderedTools[$idx];
            unset($orderedTools[$idx]);
            $orderedTools = array_values($orderedTools);
            array_unshift($orderedTools, $first);
        }

        return array_map(function (OrderedTool $orderedTool) use ($workspace, $fileBag) {
            return $this->toolProvider->export($orderedTool->getName(), WorkspaceContext::getName(), $workspace, $fileBag);
        }, $orderedTools);
    }

    private function importTools(array $data, Workspace $workspace, array $roles, FileBag $fileBag): array
    {
        $this->logger->debug('Importing the tools...');

        $createdObjects = $roles; // keep a map of old ID => new object for all imported objects
        foreach ($data['tools'] as $orderedToolData) {
            $this->logger->debug(sprintf('Importing the tool %s...', $orderedToolData['name']));

            $toolObjects = $this->toolProvider->import($orderedToolData['name'], WorkspaceContext::getName(), $workspace, $fileBag, $orderedToolData, $createdObjects);
            $createdObjects = array_merge([], $createdObjects, $toolObjects);
        }

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
