<?php

namespace Claroline\CommunityBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CommunityBundle\Finder\UserType;
use Claroline\CommunityBundle\Manager\TeamManager;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TeamMembersList extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TeamManager $teamManager
    ) {
    }

    public static function getName(): string
    {
        return 'teams-members';
    }

    public function supportsContext(string $context): bool
    {
        return WorkspaceContext::getName() === $context;
    }

    public static function getClass(): string
    {
        return UserType::class;
    }

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?bool $filterSubject = true, ?Request $request = null): FinderRequest
    {
        $finderRequest = parent::getRequest($context, $contextSubject, $filterSubject, $request);

        $user = $this->tokenStorage->getToken()?->getUser();
        $teams = [];
        if ($user) {
            $teams = $this->teamManager->getTeamsByUserAndWorkspace($user, $contextSubject);
            $teams = array_map(function (Team $team) {
                return $team->getUuid();
            }, $teams);
        }

        $teamFilter = $finderRequest->getFilter('teams');
        if (!empty($teamFilter)) {
            $teams = array_intersect($teams, is_array($teamFilter) ? $teamFilter : [$teamFilter]);
        }

        $finderRequest->addFilter('teams', $teams);

        return $finderRequest;
    }
}
