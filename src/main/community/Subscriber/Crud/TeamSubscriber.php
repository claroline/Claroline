<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CommunityBundle\Manager\TeamManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TeamSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly FileManager $fileManager,
        private readonly TeamManager $manager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Team::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Team::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Team::class) => 'postDelete',
        ];
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Team $team */
        $team = $event->getObject();
        $data = $event->getData();

        $this->createDirectoryAndRoles($team, $data);

        if ($team->getPoster()) {
            $this->fileManager->linkFile(Team::class, $team->getUuid(), $team->getPoster());
        }

        if ($team->getThumbnail()) {
            $this->fileManager->linkFile(Team::class, $team->getUuid(), $team->getThumbnail());
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Team $team */
        $team = $event->getObject();
        $data = $event->getData();
        $oldData = $event->getOldData();

        $this->createDirectoryAndRoles($team, $data);

        $this->fileManager->updateFile(
            Team::class,
            $team->getUuid(),
            $team->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        $this->fileManager->updateFile(
            Team::class,
            $team->getUuid(),
            $team->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Team $team */
        $team = $event->getObject();

        $this->om->startFlushSuite();

        // delete related roles
        $this->manager->deleteTeamRoles($team);

        // delete resources if needed
        $this->manager->deleteTeamDirectory($team);

        if ($team->getPoster()) {
            $this->fileManager->unlinkFile(Team::class, $team->getUuid(), $team->getPoster());
        }

        if ($team->getThumbnail()) {
            $this->fileManager->unlinkFile(Team::class, $team->getUuid(), $team->getThumbnail());
        }

        $this->om->endFlushSuite();
    }

    private function createDirectoryAndRoles(Team $team, array $data): void
    {
        // Checks and creates role for team members & team manager if needed.
        $teamRole = $team->getRole();
        $teamManagerRole = $team->getManagerRole();

        if (empty($teamRole)) {
            $teamRole = $this->manager->createTeamRole($team);
            $team->setRole($teamRole);

            $this->om->persist($teamRole);
        }

        if (empty($teamManagerRole)) {
            $teamManagerRole = $this->manager->createTeamRole($team, true);
            $team->setManagerRole($teamManagerRole);

            $this->om->persist($teamManagerRole);
        }

        // Checks and creates team directory
        $directory = $team->getDirectory();
        if (empty($directory)) {
            if (isset($data['createPublicDirectory']) && $data['createPublicDirectory']) {
                $defaultResource = isset($data['defaultResource']['id']) ?
                    $this->om->getRepository(ResourceNode::class)->findOneBy(['uuid' => $data['defaultResource']['id']]) :
                    null;
                $creatableResources = isset($data['creatableResources']) ? $data['creatableResources'] : [];
                $directory = $this->manager->createTeamDirectory(
                    $team,
                    $this->tokenStorage->getToken()?->getUser(),
                    $defaultResource,
                    $creatableResources
                );
                $team->setDirectory($directory->getResourceNode());
                $this->manager->initializeTeamRights($team);
            }
        } else {
            $this->manager->updateTeamDirectoryPerms($team);
        }

        $this->om->flush();
    }
}
