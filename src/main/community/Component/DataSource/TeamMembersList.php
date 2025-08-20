<?php

namespace Claroline\CommunityBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderQuery;
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

    protected function getQuery(string $context, ?ContextSubjectInterface $contextSubject = null, ?Request $request = null): FinderQuery
    {
        $finderQuery = parent::getQuery($context, $contextSubject, $request);

        $user = $this->tokenStorage->getToken()?->getUser();
        $teams = [];
        if ($user) {
            $teams = $this->teamManager->getTeamsByUserAndWorkspace($user, $contextSubject);
            $teams = array_map(function (Team $team) {
                return $team->getUuid();
            }, $teams);
        }

        $teamFilter = $finderQuery->getFilter('teams');
        if (!empty($teamFilter)) {
            $teams = array_intersect($teams, is_array($teamFilter) ? $teamFilter : [$teamFilter]);
        }

        $finderQuery->addFilter('teams', $teams);

        return $finderQuery;
    }
}
