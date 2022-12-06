<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Subscriber\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CommunityBundle\Manager\TeamManager;
use Claroline\CommunityBundle\Serializer\ProfileSerializer;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\ConfigureToolEvent;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\ImportToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommunitySubscriber implements EventSubscriberInterface
{
    const NAME = 'community';

    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;
    /** @var ParametersSerializer */
    private $parametersSerializer;
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var UserManager */
    private $userManager;
    /** @var TeamManager */
    private $teamManager;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        ParametersSerializer $parametersSerializer,
        ProfileSerializer $profileSerializer,
        UserManager $userManager,
        TeamManager $teamManager
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->parametersSerializer = $parametersSerializer;
        $this->profileSerializer = $profileSerializer;
        $this->userManager = $userManager;
        $this->teamManager = $teamManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::DESKTOP, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::WORKSPACE, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::EXPORT, AbstractTool::WORKSPACE, static::NAME) => 'onExport',
            ToolEvents::getEventName(ToolEvents::IMPORT, AbstractTool::WORKSPACE, static::NAME) => 'onImport',
        ];
    }

    public function onOpen(OpenToolEvent $event)
    {
        $event->setData([
            'profile' => $this->profileSerializer->serialize(),
            'parameters' => $this->parametersSerializer->serialize(),
            'usersLimitReached' => $this->userManager->hasReachedLimit(),
        ]);

        $event->stopPropagation();
    }

    public function onConfigure(ConfigureToolEvent $event)
    {
        $parameters = $event->getParameters();
        if (isset($parameters['parameters'])) {
            if (!empty($event->getWorkspace())) {
                // configure workspace tool
            } else {
                // configure desktop tool
            }

            // send updated data to the caller
            $event->setData([
                'parameters' => $this->parametersSerializer->serialize(),
            ]);

            $event->stopPropagation();
        }
    }

    public function onExport(ExportToolEvent $event)
    {
        $teams = $this->om->getRepository(Team::class)->findBy(['workspace' => $event->getWorkspace()]);

        $event->setData([
            'teams' => array_map(function (Team $team) {
                return $this->serializer->serialize($team);
            }, $teams),
        ]);
    }

    public function onImport(ImportToolEvent $event)
    {
        $data = $event->getData();

        if (empty($data['teams'])) {
            return;
        }

        $this->om->startFlushSuite();

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

            if (!empty($teamData['managerRole']) && $event->getCreatedEntity($teamData['managerRole']['id'])) {
                $teamData['managerRole'] = [
                    'id' => $event->getCreatedEntity($teamData['managerRole']['id'])->getUuid(),
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
