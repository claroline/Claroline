<?php

namespace Claroline\CoreBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\CodeNormalizer;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\Workspace\TransferManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly WorkspaceManager $manager,
        private readonly FileManager $fileManager,
        private readonly ResourceManager $resourceManager,
        private readonly OrganizationManager $organizationManager,
        private readonly WorkspaceSerializer $serializer,
        private readonly TransferManager $transferManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Workspace::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Workspace::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::PRE_UPDATE, Workspace::class) => 'preUpdate',
            CrudEvents::getEventName(CrudEvents::PRE_COPY, Workspace::class) => 'preCopy',
            CrudEvents::getEventName(CrudEvents::POST_COPY, Workspace::class) => 'postCopy',
            CrudEvents::getEventName(CrudEvents::PRE_DELETE, Workspace::class) => 'preDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();
        $data = $event->getData();
        $options = $event->getOptions();

        // make sure the workspace code is unique and generate one if missing
        $workspaceCode = $this->manager->getUniqueCode(
            $workspace->getCode() ?? CodeNormalizer::normalize($workspace->getName())
        );
        $workspace->setCode($workspaceCode);

        // copy model data
        if (!empty($workspace->getWorkspaceModel())) {
            // inject model data inside the new workspace
            $this->copy($workspace->getWorkspaceModel(), $workspace, in_array(Options::AS_MODEL, $options) || $workspace->isModel());

            // we need to override model values with the posted one
            // this is not really aesthetic because this has already been done by the Crud before
            // and workspace deserialization is heavy
            $this->serializer->deserialize($data, $workspace, $options);
            if ($workspaceCode) {
                $workspace->setCode($workspaceCode);
            }
        }

        $this->handleNewWorkspace($workspace);
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        // give the creator the manager role
        /*if (!$workspace->isModel() && !$workspace->isPersonal() && $workspace->getManagerRole() && $workspace->getCreator()) {
            $this->crud->patch($workspace->getCreator(), 'role', 'add', [$workspace->getManagerRole()]);
        }*/

        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        if ($root) {
            $this->resourceManager->createRights($root, [], true, false);
        }
    }

    public function preCopy(CopyEvent $event): void
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

    public function postCopy(CopyEvent $event): void
    {
        /** @var Workspace $workspace */
        $workspace = $event->getCopy();

        // give the creator the manager role
        /*if (!$workspace->isModel() && $workspace->getManagerRole() && $workspace->getCreator()) {
            $this->crud->patch($workspace->getCreator(), 'role', 'add', [$workspace->getManagerRole()]);
        }*/

        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        if ($root) {
            $this->resourceManager->createRights($root, [], true, false);
        }
    }

    public function preUpdate(UpdateEvent $event): void
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
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        $this->fileManager->updateFile(
            Workspace::class,
            $workspace->getUuid(),
            $workspace->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );
    }

    public function preDelete(DeleteEvent $event): void
    {
        $workspace = $event->getObject();

        $roots = $this->om->getRepository(ResourceNode::class)->findBy(['workspace' => $workspace, 'parent' => null]);
        if (!empty($roots)) {
            $this->crud->deleteBulk($roots, [Crud::NO_PERMISSIONS]);
        }
    }

    public function postDelete(DeleteEvent $event): void
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

    private function handleNewWorkspace(Workspace $workspace): void
    {
        // timestamp workspace
        $workspace->setCreatedAt(new \DateTime());
        $workspace->setUpdatedAt(new \DateTime());

        // set the creator
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        if ($user instanceof User) {
            $workspace->setCreator($user);

            if (empty($workspace->getOrganizations()->toArray()) && !empty($user->getMainOrganization())) {
                $workspace->addOrganization($user->getMainOrganization());
            }
        }

        // adds default organization if needed
        if (empty($workspace->getOrganizations()->toArray())) {
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

        return $workspaceCopy;
    }
}
