<?php

namespace Claroline\TeamBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\TeamBundle\Entity\Team;
use Claroline\TeamBundle\Manager\TeamManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TeamCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var TeamManager */
    private $manager;

    public function __construct(TokenStorageInterface $tokenStorage, ObjectManager $om, TeamManager $manager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->manager = $manager;
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var Team $team */
        $team = $event->getObject();
        $data = $event->getData();

        $this->createDirectoryAndRoles($team, $data);
    }

    public function postUpdate(UpdateEvent $event)
    {
        /** @var Team $team */
        $team = $event->getObject();
        $data = $event->getData();

        $this->createDirectoryAndRoles($team, $data);
    }

    public function preDelete(DeleteEvent $event)
    {
        /** @var Team $team */
        $team = $event->getObject();

        $this->om->startFlushSuite();

        // delete related roles
        $this->manager->deleteTeamRoles($team);

        // delete resources if needed
        $this->manager->deleteTeamDirectory($team);

        $this->om->endFlushSuite();
    }

    private function createDirectoryAndRoles(Team $team, array $data)
    {
        // Checks and creates role for team members & team manager if needed.
        $teamRole = $team->getRole();
        $teamManagerRole = $team->getTeamManagerRole();

        if (empty($teamRole)) {
            $teamRole = $this->manager->createTeamRole($team);
            $team->setRole($teamRole);

            $this->om->persist($teamRole);
        }

        if (empty($teamManagerRole)) {
            $teamManagerRole = $this->manager->createTeamRole($team, true);
            $team->setTeamManagerRole($teamManagerRole);

            $this->om->persist($teamManagerRole);
        }

        // Checks and creates team directory
        $directory = $team->getDirectory();
        if (empty($directory)) {
            if (isset($data['createPublicDirectory']) && $data['createPublicDirectory']) {
                $defaultResource = isset($data['defaultResource']['id']) ?
                    $this->om->getRepository(ResourceNode::class)->findOneBy(['uuid' => $data['defaultResource']['id']]) :
                    null;
                $creatableResources = isset($data['creatableResources']) ?
                    $data['creatableResources'] :
                    [];
                $directory = $this->manager->createTeamDirectory(
                    $team,
                    $this->tokenStorage->getToken()->getUser(),
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
