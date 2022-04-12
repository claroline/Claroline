<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Subscriber\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\ImportToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\TeamBundle\Entity\Team;
use Claroline\TeamBundle\Entity\WorkspaceTeamParameters;
use Claroline\TeamBundle\Manager\TeamManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TeamSubscriber implements EventSubscriberInterface
{
    const NAME = 'claroline_team_tool';

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;
    /** @var TeamManager */
    private $teamManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        TeamManager $teamManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->teamManager = $teamManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::WORKSPACE, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::EXPORT, AbstractTool::WORKSPACE, static::NAME) => 'onExport',
            ToolEvents::getEventName(ToolEvents::IMPORT, AbstractTool::WORKSPACE, static::NAME) => 'onImport',
        ];
    }

    public function onOpen(OpenToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $user = $this->tokenStorage->getToken()->getUser();

        $myTeams = [];
        if ($user instanceof User) {
            $myTeams = $this->teamManager->getTeamsByUserAndWorkspace($user, $workspace);
        }

        $event->setData([
            // this can be retrieved from serialized tool data in ui. to remove
            'canEdit' => $this->authorization->isGranted([static::NAME, 'edit'], $workspace),
            'teamParams' => $this->serializer->serialize(
                $this->teamManager->getWorkspaceTeamParameters($workspace)
            ),
            'myTeams' => array_map(function (Team $team) {
                return $team->getUuid();
            }, $myTeams),
        ]);
        $event->stopPropagation();
    }

    public function onExport(ExportToolEvent $event)
    {
        $teams = $this->om->getRepository(Team::class)->findBy(['workspace' => $event->getWorkspace()]);

        $event->setData([
            'parameters' => $this->serializer->serialize(
                $this->teamManager->getWorkspaceTeamParameters($event->getWorkspace())
            ),
            'teams' => array_map(function (Team $team) {
                return $this->serializer->serialize($team);
            }, $teams),
        ]);
    }

    public function onImport(ImportToolEvent $event)
    {
        $data = $event->getData();

        if (empty($data['teams']) && empty($data['parameters'])) {
            return;
        }

        $this->om->startFlushSuite();

        // import tool parameters
        $parameters = new WorkspaceTeamParameters();
        if (!empty($data['parameters'])) {
            $this->serializer->deserialize($data['parameters'], $parameters, [Options::REFRESH_UUID]);
        }

        $parameters->setWorkspace($event->getWorkspace());
        $this->om->persist($parameters);

        // import teams
        foreach ($data['teams'] as $teamData) {
            // correct relation to external entities
            if (isset($teamData['workspace'])) {
                unset($teamData['workspace']);
            }

            if (!empty($teamData['directory']) && $event->getCreatedEntity($teamData['directory']['id'])) {
                $teamData['directory'] = [
                    'id' => $event->getCreatedEntity($teamData['directory']['id'])->getUuid(),
                ];
            }

            if (!empty($teamData['role']) && $event->getCreatedEntity($teamData['role']['id'])) {
                $teamData['role'] = [
                    'id' => $event->getCreatedEntity($teamData['role']['id'])->getUuid(),
                ];
            }

            if (!empty($teamData['teamManagerRole']) && $event->getCreatedEntity($teamData['teamManagerRole']['id'])) {
                $teamData['teamManagerRole'] = [
                    'id' => $event->getCreatedEntity($teamData['teamManagerRole']['id'])->getUuid(),
                ];
            }

            $team = new Team();
            $team->setWorkspace($event->getWorkspace());

            $this->crud->create($team, $teamData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

            $event->addCreatedEntity($teamData['id'], $team);
        }

        $this->om->endFlushSuite();
    }
}
