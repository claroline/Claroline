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
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CommunityBundle\Manager\TeamManager;
use Claroline\CommunityBundle\Serializer\ProfileSerializer;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\ConfigureToolEvent;
use Claroline\CoreBundle\Event\Tool\ExportToolEvent;
use Claroline\CoreBundle\Event\Tool\ImportToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CommunitySubscriber implements EventSubscriberInterface
{
    const NAME = 'community';

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ParametersSerializer */
    private $parametersSerializer;
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var UserManager */
    private $userManager;
    /** @var RoleManager */
    private $roleManager;
    /** @var TeamManager */
    private $teamManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        PlatformConfigurationHandler $config,
        ParametersSerializer $parametersSerializer,
        ProfileSerializer $profileSerializer,
        UserManager $userManager,
        RoleManager $roleManager,
        TeamManager $teamManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->config = $config;
        $this->parametersSerializer = $parametersSerializer;
        $this->profileSerializer = $profileSerializer;
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->teamManager = $teamManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::DESKTOP, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::WORKSPACE, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::CONFIGURE, AbstractTool::DESKTOP, static::NAME) => 'onConfigure',
            ToolEvents::getEventName(ToolEvents::CONFIGURE, AbstractTool::WORKSPACE, static::NAME) => 'onConfigure',
            ToolEvents::getEventName(ToolEvents::EXPORT, AbstractTool::WORKSPACE, static::NAME) => 'onExport',
            ToolEvents::getEventName(ToolEvents::IMPORT, AbstractTool::WORKSPACE, static::NAME) => 'onImport',
        ];
    }

    public function onOpen(OpenToolEvent $event): void
    {
        $userTeams = [];
        if ($this->tokenStorage->getToken()->getUser() instanceof User && $event->getWorkspace()) {
            $userTeams = $this->teamManager->getTeamsByUserAndWorkspace($this->tokenStorage->getToken()->getUser(), $event->getWorkspace());
        }

        $event->setData([
            'userTeams' => array_map(function (Team $team) {
                return $this->serializer->serialize($team, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $userTeams),
            'profile' => $this->profileSerializer->serialize(),
            'usersLimitReached' => $this->userManager->hasReachedLimit(),
            // for retro compatibility :
            //  - in workspace tool, configuration is stored in the workspace entity
            //  - in desktop tool, configuration is stored in the platform options
            // to remove when the community options get their own entity
            'parameters' => $event->getWorkspace() ? $this->getWorkspaceParameters($event->getWorkspace()) : $this->getDesktopParameters(),
        ]);

        $event->stopPropagation();
    }

    public function onConfigure(ConfigureToolEvent $event): void
    {
        $parameters = $event->getParameters();
        if (isset($parameters['parameters'])) {
            if (!empty($event->getWorkspace())) {
                // configure workspace tool
                $updatedParameters = $this->updateWorkspaceParameters($parameters['parameters'], $event->getWorkspace());
            } else {
                // configure desktop tool
                $updatedParameters = $this->updateDesktopParameters($parameters['parameters']);
            }

            // send updated data to the caller
            $event->setData([
                'parameters' => $updatedParameters,
            ]);

            $event->stopPropagation();
        }
    }

    public function onExport(ExportToolEvent $event): void
    {
        $teams = $this->om->getRepository(Team::class)->findBy(['workspace' => $event->getWorkspace()]);

        $event->setData([
            'teams' => array_map(function (Team $team) {
                return $this->serializer->serialize($team);
            }, $teams),
        ]);
    }

    public function onImport(ImportToolEvent $event): void
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

    private function updateDesktopParameters(array $parametersData): array
    {
        // only keep parameters linked to community to avoid exposing all the platform parameters here
        $communityParameters = [];
        if (isset($parametersData['registration'])) {
            $communityParameters['registration'] = $parametersData['registration'];

            // only store default role name in platform options
            if (!empty($parametersData['registration']['default_role'])) {
                $communityParameters['registration']['default_role'] = $parametersData['registration']['default_role']['name'];
            }
        }
        if (isset($parametersData['authentication'])) {
            $communityParameters['authentication'] = $parametersData['authentication'];
        }
        if (isset($parametersData['profile'])) {
            $communityParameters['profile'] = $parametersData['profile'];
        }
        if (isset($parametersData['community'])) {
            $communityParameters['community'] = $parametersData['community'];
        }

        // removes locked parameters values if any
        $locked = $this->config->getParameter('lockedParameters') ?? [];
        foreach ($locked as $lockedParam) {
            ArrayUtils::remove($communityParameters, $lockedParam);
        }

        // save updated parameters
        $this->parametersSerializer->deserialize($communityParameters);

        return $this->parametersSerializer->serialize();
    }

    private function updateWorkspaceParameters(array $parametersData, Workspace $workspace): array
    {
        // only keep parameters linked to community to avoid exposing all the workspace parameters here
        $communityParameters = [];
        if (isset($parametersData['registration'])) {
            $communityParameters['registration'] = $parametersData['registration'];
        }

        $this->crud->update($workspace, $communityParameters, [Crud::THROW_EXCEPTION]);

        return $this->serializer->serialize($workspace);
    }

    private function getWorkspaceParameters(Workspace $workspace): array
    {
        $parameters = $this->serializer->serialize($workspace);

        // only grab workspace props we want
        return [
            'registration' => $parameters['registration'],
        ];
    }

    private function getDesktopParameters(): array
    {
        $parameters = $this->parametersSerializer->serialize();

        // load default role entity for UI rendering
        $defaultRoleName = ArrayUtils::get($parameters, 'registration.default_role') ?? PlatformRoles::USER;
        $roleUser = $this->roleManager->getRoleByName($defaultRoleName);
        if (empty($parameters['registration'])) {
            $parameters['registration'] = [];
        }
        $parameters['registration']['default_role'] = $this->serializer->serialize($roleUser, [SerializerInterface::SERIALIZE_MINIMAL]);

        // only grab platform options we want
        return [
            'registration' => $parameters['registration'],
            'authentication' => $parameters['authentication'],
            'profile' => $parameters['profile'],
            'community' => $parameters['community'],
        ];
    }
}
