<?php

namespace Claroline\CommunityBundle\Subscriber\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CommunityBundle\Manager\TeamManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeamMemberSourceSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private FinderProvider $finder;
    private TeamManager $teamManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder,
        TeamManager $teamManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
        $this->teamManager = $teamManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'data_source.teams-members.load' => 'getData',
        ];
    }

    public function getData(GetDataEvent $event): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        $teams = $this->teamManager->getTeamsByUserAndWorkspace($user, $event->getWorkspace());

        $teamUuids = array_map(function ($team) {
            return $team->getUuid();
        }, $teams);

        $filters = $event->getOptions()['filters'] ?? [];
        $teamFilter = $filters['team'] ?? [];

        if ('string' === gettype($teamFilter)) {
            $teamFilter = [$teamFilter];
        }
        if (!empty($teamFilter)) {
            $teamUuids = array_intersect($teamUuids, $teamFilter);
        }

        $teamMembers = $this->finder->search(User::class, ['hiddenFilters' => ['teams' => $teamUuids]]);

        $event->setData($teamMembers);
        $event->stopPropagation();
    }
}
