<?php

namespace Claroline\CommunityBundle\Component\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CommunityBundle\Manager\TeamManager;
use Claroline\CommunityBundle\Serializer\ProfileSerializer;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CommunityTool extends AbstractTool
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly PlatformConfigurationHandler $config,
        private readonly ParametersSerializer $parametersSerializer,
        private readonly ProfileSerializer $profileSerializer,
        private readonly UserManager $userManager,
        private readonly RoleManager $roleManager,
        private readonly TeamManager $teamManager
    ) {
    }

    public static function getName(): string
    {
        return 'community';
    }

    public static function getIcon(): string
    {
        return 'users';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            DesktopContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        $userTeams = [];
        if ($this->tokenStorage->getToken()->getUser() instanceof User && $context === WorkspaceContext::getName()) {
            $userTeams = $this->teamManager->getTeamsByUserAndWorkspace($this->tokenStorage->getToken()->getUser(), $contextSubject);
        }

        return [
            'userTeams' => array_map(function (Team $team) {
                return $this->serializer->serialize($team, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $userTeams),
            'profile' => $this->profileSerializer->serialize(),
            'usersLimitReached' => $this->userManager->hasReachedLimit(),
            'parameters' => $contextSubject ? $this->getWorkspaceParameters($contextSubject) : $this->getDesktopParameters(),
        ];
    }

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        $this->om->startFlushSuite();

        if (!empty($configData['parameters'])) {
            if (!empty($contextSubject)) {
                // configure workspace tool
                $this->updateWorkspaceParameters($configData['parameters'], $contextSubject);
            } else {
                // configure desktop tool
                $this->updateDesktopParameters($configData['parameters']);
            }
        }

        if (!empty($configData['profile'])) {
            $this->updateProfile($configData['profile']);
        }

        $this->om->endFlushSuite();

        return [
            'profile' => $this->profileSerializer->serialize(),
            'parameters' => $contextSubject ? $this->getWorkspaceParameters($contextSubject) : $this->getDesktopParameters(),
        ];
    }

    public function export(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): ?array
    {
        if (WorkspaceContext::getName() !== $context) {
            return [];
        }

        $teams = $this->om->getRepository(Team::class)->findBy(['workspace' => $contextSubject]);

        return [
            'teams' => array_map(function (Team $team) {
                return $this->serializer->serialize($team);
            }, $teams),
        ];
    }

    public function import(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null, array $data = [], array $entities = []): ?array
    {
        if (WorkspaceContext::getName() !== $context) {
            return [];
        }

        if (empty($data['teams'])) {
            return [];
        }

        $this->om->startFlushSuite();

        // import teams
        foreach ($data['teams'] as $teamData) {
            // correct relation to external entities
            if (isset($teamData['workspace'])) {
                unset($teamData['workspace']);
            }

            if (!empty($teamData['directory']) && $entities[$teamData['directory']['id']]) {
                $teamData['directory'] = [
                    'id' => $entities[$teamData['directory']['id']]->getUuid(),
                ];
            }

            if (!empty($teamData['role']) && $entities[$teamData['role']['id']]) {
                $teamData['role'] = [
                    'id' => $entities[$teamData['role']['id']]->getUuid(),
                ];
            }

            if (!empty($teamData['managerRole']) && $entities[$teamData['managerRole']['id']]) {
                $teamData['managerRole'] = [
                    'id' => $entities[$teamData['managerRole']['id']]->getUuid(),
                ];
            }

            $team = new Team();
            $team->setWorkspace($contextSubject);

            $this->crud->create($team, $teamData, [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION, Options::REFRESH_UUID]);

            $entities[$teamData['id']] = $team;
        }

        $this->om->endFlushSuite();

        return $entities;
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
        $registration = $this->config->getParameter('registration') ?? [];

        // load default role entity for UI rendering
        $defaultRoleName = $registration['default_role'] ?? PlatformRoles::USER;
        $defaultRole = $this->roleManager->getRoleByName($defaultRoleName);
        $registration['default_role'] = $this->serializer->serialize($defaultRole, [SerializerInterface::SERIALIZE_MINIMAL]);

        // only grab platform options we want
        return [
            'registration' => $registration ?? [],
            'profile' => $this->config->getParameter('profile')  ?? [],
            'community' => $this->config->getParameter('community') ?? [],
        ];
    }

    private function updateDesktopParameters(array $parametersData): void
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
    }

    private function updateWorkspaceParameters(array $parametersData, Workspace $workspace): void
    {
        // only keep parameters linked to community to avoid exposing all the workspace parameters here
        $communityParameters = [];
        if (isset($parametersData['registration'])) {
            $communityParameters['registration'] = $parametersData['registration'];
        }

        $this->crud->update($workspace, $communityParameters);
    }

    private function updateProfile(array $profileData): void
    {
        // dump current profile configuration (to know what to remove later)
        /** @var Facet[] $facets */
        $facets = $this->om->getRepository(Facet::class)->findAll();

        $this->om->startFlushSuite();

        // updates facets data
        $updatedFacets = [];
        foreach ($profileData as $facetData) {
            $updated = $this->crud->update(Facet::class, $facetData);
            $updatedFacets[$updated->getId()] = $updated;
        }

        // removes deleted facets
        foreach ($facets as $facet) {
            if (empty($updatedFacets[$facet->getId()])) {
                $this->crud->delete($facet);
            }
        }

        $this->om->endFlushSuite();
    }
}
