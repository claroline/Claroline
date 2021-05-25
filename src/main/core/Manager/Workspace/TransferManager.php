<?php

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Listener\Log\LogListener;
use Claroline\CoreBundle\Manager\Workspace\Transfer\OrderedToolTransfer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Psr\Log\LoggerAwareInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TransferManager implements LoggerAwareInterface
{
    use PermissionCheckerTrait;
    use LoggableTrait;

    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var TempFileManager */
    private $tempFileManager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var FinderProvider */
    private $finder;
    /** @var Crud */
    private $crud;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var OrderedToolTransfer */
    private $ots;
    /** @var FileUtilities */
    private $fileUts;
    /** @var LogListener */
    private $logListener;

    /**
     * TransferManager constructor.
     */
    public function __construct(
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        TempFileManager $tempFileManager,
        SerializerProvider $serializer,
        OrderedToolTransfer $ots,
        FinderProvider $finder,
        Crud $crud,
        TokenStorageInterface $tokenStorage,
        FileUtilities $fileUts,
        LogListener $logListener,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->tempFileManager = $tempFileManager;
        $this->serializer = $serializer;
        $this->finder = $finder;
        $this->crud = $crud;
        $this->tokenStorage = $tokenStorage;
        $this->ots = $ots;
        $this->fileUts = $fileUts;
        $this->logListener = $logListener;
        $this->authorization = $authorization;
    }

    /**
     * @param array $data - the serialized data of the object to create
     *
     * @return object
     */
    public function create(array $data, Workspace $workspace)
    {
        $options = [Options::REFRESH_UUID];
        // gets entity from raw data.
        $workspace = $this->deserialize($data, $workspace, $options);
        // creates the entity if allowed
        $this->checkPermission('CREATE', $workspace, [], true);

        if ($this->dispatch('create', 'pre', [$workspace, $options])) {
            $this->om->save($workspace);
            $this->dispatch('create', 'post', [$workspace, $options]);
        }

        return $workspace;
    }

    public function dispatch($action, $when, array $args)
    {
        return $this->crud->dispatch($action, $when, $args);
    }

    public function export(Workspace $workspace)
    {
        $fileBag = new FileBag();
        $data = $this->serialize($workspace);
        $data = $this->exportFiles($data, $fileBag, $workspace);
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
    public function serialize(Workspace $workspace)
    {
        $serialized = $this->serializer->serialize($workspace, [Options::REFRESH_UUID]);

        // we want to load the resources first
        /** @var OrderedTool[] $ot */
        $ot = $workspace->getOrderedTools()->toArray();

        $idx = 0;

        foreach ($ot as $key => $tool) {
            if ('resources' === $tool->getTool()->getName()) {
                $idx = $key;
            }
        }

        $first = $ot[$idx];
        unset($ot[$idx]);
        array_unshift($ot, $first);

        $serialized['orderedTools'] = array_map(function (OrderedTool $tool) {
            $data = $this->ots->serialize($tool, [Options::SERIALIZE_TOOL, Options::REFRESH_UUID]);

            return $data;
        }, $ot);

        return $serialized;
    }

    /**
     * Deserializes Workspace data into entities.
     *
     * @param FileBag $bag
     *
     * @return Workspace
     */
    public function deserialize(array $data, Workspace $workspace, array $options = [], FileBag $bag = null)
    {
        $this->logListener->disable();
        $data = $this->replaceResourceIds($data);

        $defaultRole = $data['registration']['defaultRole'];

        unset($data['registration']['defaultRole']);
        //we don't want new workspaces to be considered as models
        $data['meta']['model'] = false;

        /** @var Workspace $workspace */
        $workspace = $this->serializer->deserialize($data, $workspace, $options);
        $this->om->persist($workspace);

        $this->log('Deserializing the roles...');
        $roles = [];
        foreach ($data['roles'] as $roleData) {
            $roleData['workspace']['id'] = $workspace->getUuid();
            $role = new Role();
            $this->om->persist($role);

            $role->setWorkspace($workspace);
            $workspace->addRole($role);

            $roles[] = $this->crud->create($role, $roleData, [Crud::NO_PERMISSIONS, Options::FORCE_FLUSH]);
        }

        foreach ($roles as $role) {
            if ($defaultRole['translationKey'] === $role->getTranslationKey()) {
                $workspace->setDefaultRole($role);
            }
        }

        $this->om->forceFlush();

        $data['root']['meta']['workspace']['id'] = $workspace->getUuid();

        $this->log('Get filebag');

        if (!$bag) {
            $bag = $this->getFileBag($data);
        }

        $this->log('Pre import data update...');

        foreach ($data['orderedTools'] as $orderedToolData) {
            $this->ots->setLogger($this->logger);
            $data = $this->ots->dispatchPreEvent($data, $orderedToolData);
        }

        $this->log('Deserializing the tools...');

        foreach ($data['orderedTools'] as $orderedToolData) {
            $orderedTool = new OrderedTool();
            $this->ots->setLogger($this->logger);
            $this->ots->deserialize($orderedToolData, $orderedTool, [], $workspace, $bag);
        }

        if (!$workspace->getCreator() && $this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
            $workspace->setCreator($this->tokenStorage->getToken()->getUser());
        }

        $this->logListener->enable();

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

    private function getFileBag(array $data = [])
    {
        $filebag = new FileBag();

        if (isset($data['archive'])) {
            $this->log('Get filebag from the archive...');
            $object = $this->om->getObject($data['archive'], PublicFile::class);

            $archive = new \ZipArchive();
            if ($archive->open($this->fileUts->getPath($object))) {
                $dest = sys_get_temp_dir().'/'.uniqid();
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

                    $filebag->add($fileName, $location);
                }
            }
        }

        return $filebag;
    }

    //todo: move in resourcemanager tool transfer
    public function replaceResourceIds($serialized)
    {
        $replaced = json_encode($serialized);

        foreach ($serialized['orderedTools'] as $tool) {
            if ('resources' === $tool['tool']) {
                $nodes = $tool['data']['nodes'];

                foreach ($nodes as $data) {
                    $uuid = Uuid::uuid4()->toString();

                    if (isset($data['id'])) {
                        $replaced = str_replace($data['id'], $uuid, $replaced);
                        $this->log('Replacing id '.$data['id'].' by '.$uuid);
                    }
                }
            }
        }

        return json_decode($replaced, true);
    }
}
