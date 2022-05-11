<?php

namespace Claroline\CoreBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\TransferManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;
    private $om;
    private $crud;
    private $manager;
    private $fileManager;
    private $toolManager;
    private $resourceManager;
    private $organizationManager;
    private $serializer;
    private $transferManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        Crud $crud,
        WorkspaceManager $manager,
        FileManager $fileManager,
        ToolManager $toolManager,
        ResourceManager $resourceManager,
        OrganizationManager $organizationManager,
        WorkspaceSerializer $serializer,
        TransferManager $transferManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->crud = $crud;
        $this->manager = $manager;
        $this->fileManager = $fileManager;
        $this->toolManager = $toolManager;
        $this->resourceManager = $resourceManager;
        $this->organizationManager = $organizationManager;
        $this->serializer = $serializer;
        $this->transferManager = $transferManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Workspace::class) => 'preCreate',
            Crud::getEventName('create', 'post', Workspace::class) => 'postCreate',
            Crud::getEventName('update', 'pre', Workspace::class) => 'preUpdate',
            Crud::getEventName('copy', 'pre', Workspace::class) => 'preCopy',
            Crud::getEventName('copy', 'post', Workspace::class) => 'postCopy',
            Crud::getEventName('delete', 'pre', Workspace::class) => 'preDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();
        $data = $event->getData();
        $options = $event->getOptions();

        // make sure the workspace code is unique
        if (!empty($workspace->getCode())) {
            $workspace->setCode($this->manager->getUniqueCode($workspace->getCode()));
        }

        // copy model data
        if (!in_array(Options::NO_MODEL, $options)) {
            // The NO_MODEL options is only here for workspace import.
            // It's not possible for now to create a workspace without a model (it will miss some required data).
            if (empty($workspace->getWorkspaceModel())) {
                $workspace->setWorkspaceModel($this->manager->getDefaultModel($workspace->isPersonal()));
            }

            // inject model data inside the new workspace
            $this->copy($workspace->getWorkspaceModel(), $workspace, in_array(Options::AS_MODEL, $options) || $workspace->isModel());

            // we need to override model values with the posted one
            // this is not really aesthetic because this has already be done by the Crud before
            // and workspace deserialization is heavy
            $this->serializer->deserialize($data, $workspace, $options);
        }

        $this->handleNewWorkspace($workspace);
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        // give the creator the manager role
        if (!$workspace->isModel() && !$workspace->isPersonal() && $workspace->getManagerRole() && $workspace->getCreator()) {
            $this->crud->patch($workspace->getCreator(), 'role', 'add', [$workspace->getManagerRole()]);
        }

        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        if ($root) {
            $this->resourceManager->createRights($root, [], true, false);
        }

        $this->toolManager->addMissingWorkspaceTools($workspace);
    }

    public function preCopy(CopyEvent $event)
    {
        $options = $event->getOptions();

        /** @var Workspace $original */
        $original = $event->getObject();
        /** @var Workspace $copy */
        $copy = $event->getCopy();

        // make sure the workspace code is unique
        if (!empty($copy->getCode())) {
            $copy->setCode($this->manager->getUniqueCode($copy->getCode()));
        }

        $this->copy($original, $copy, in_array(Options::AS_MODEL, $options));

        $this->handleNewWorkspace($copy);
    }

    public function postCopy(CopyEvent $event)
    {
        /** @var Workspace $workspace */
        $workspace = $event->getCopy();

        // give the creator the manager role
        if (!$workspace->isModel() && $workspace->getManagerRole() && $workspace->getCreator()) {
            $this->crud->patch($workspace->getCreator(), 'role', 'add', [$workspace->getManagerRole()]);
        }

        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        if ($root) {
            $this->resourceManager->createRights($root, [], true, false);
        }

        //$this->toolManager->addMissingWorkspaceTools($workspace);
    }

    public function preUpdate(UpdateEvent $event)
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        $workspace->setUpdatedAt(new \DateTime());

        // rename workspace root directory when workspace name is changed
        $oldData = $event->getOldData();
        if (!empty($oldData['name']) && $oldData['name'] !== $workspace->getName()) {
            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            if ($root) {
                $root->setName($workspace->getName());
                $this->om->persist($root);
            }
        }

        $this->fileManager->updateFile(
            Workspace::class,
            $workspace->getUuid(),
            $workspace->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster']['url'] : null
        );

        $this->fileManager->updateFile(
            Workspace::class,
            $workspace->getUuid(),
            $workspace->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail']['url'] : null
        );
    }

    public function preDelete(DeleteEvent $event)
    {
        $workspace = $event->getObject();

        $roots = $this->om->getRepository(ResourceNode::class)->findBy(['workspace' => $workspace, 'parent' => null]);
        if (!empty($roots)) {
            $this->crud->deleteBulk($roots, [Crud::NO_PERMISSIONS]);
        }
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        if ($workspace->getPoster()) {
            $this->fileManager->unlinkFile(Workspace::class, $workspace->getUuid(), $workspace->getPoster());
        }

        if ($workspace->getThumbnail()) {
            $this->fileManager->unlinkFile(Workspace::class, $workspace->getUuid(), $workspace->getThumbnail());
        }

        // remove workspace files dir
        rmdir($this->manager->getStorageDirectory($workspace));
    }

    private function handleNewWorkspace(Workspace $workspace)
    {
        // timestamp workspace
        $workspace->setCreatedAt(new \DateTime());
        $workspace->setUpdatedAt(new \DateTime());

        // set the creator
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        if ($user instanceof User) {
            $workspace->setCreator($user);

            if (empty($workspace->getOrganizations()) && !empty($user->getMainOrganization())) {
                $workspace->addOrganization($user->getMainOrganization());
            }
        }

        // adds default organization if needed
        if (empty($workspace->getOrganizations())) {
            $workspace->addOrganization($this->organizationManager->getDefault());
        }

        $this->fileManager->updateFile(Workspace::class, $workspace->getUuid(), $workspace->getPoster());
        $this->fileManager->updateFile(Workspace::class, $workspace->getUuid(), $workspace->getThumbnail());
    }

    private function copy(Workspace $workspace, Workspace $newWorkspace, ?bool $model = false): Workspace
    {
        $fileBag = new FileBag();
        $data = $this->transferManager->serialize($workspace, $fileBag);

        $workspaceCopy = $this->transferManager->deserialize($data, $newWorkspace, $fileBag);

        $workspaceCopy->setModel($model);

        // Copy workspace shortcuts
        /** @var Shortcuts[] $workspaceShortcuts */
        $workspaceShortcuts = $this->om->getRepository(Shortcuts::class)->findBy(['workspace' => $workspace]);

        foreach ($workspaceShortcuts as $shortcuts) {
            $role = $shortcuts->getRole();

            $roleName = preg_replace('/'.$workspace->getUuid().'$/', '', $role->getName()).$workspaceCopy->getUuid();
            $roleCopy = $this->om->getRepository(Role::class)->findOneBy(['name' => $roleName]);

            if ($roleCopy) {
                $shortcutsCopy = new Shortcuts();
                $shortcutsCopy->setWorkspace($workspaceCopy);
                $shortcutsCopy->setRole($roleCopy);
                $shortcutsCopy->setData($shortcuts->getData());
                $this->om->persist($shortcutsCopy);
            }
        }

        return $workspaceCopy;
    }
}
