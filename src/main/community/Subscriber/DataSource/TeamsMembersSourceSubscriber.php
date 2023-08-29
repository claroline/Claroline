<?php

namespace Claroline\CommunityBundle\Subscriber\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CommunityBundle\Manager\TeamManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TeamsMembersSourceSubscriber implements EventSubscriberInterface
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
        $user = $this->tokenStorage->getToken()->getUser();
        $teams = $this->teamManager->getTeamsByUserAndWorkspace($user, $event->getWorkspace());

        $options = $event->getOptions();
        $filters = $options['filters'] ?? [];
        $isFilteredByTeam = array_key_exists('team', $filters) && !empty($filters['team']);

        $teamMembers = [];
        foreach ($teams as $team) {
            if ($isFilteredByTeam && !in_array($team->getUuid(), $filters['team'])) {
                continue;
            }
            foreach ($team->getUsers() as $teamUser) {
                $teamMembers[] = $teamUser;
            }
        }

        $teamIds = array_unique(array_map(function ($teamMember) use ($user) {
            return $teamMember->getUuid() !== $user->getUuid() ? $teamMember->getUuid() : null;
        }, $teamMembers));

        $teamMembers = $this->finder->search(User::class, ['hiddenFilters' => ['id' => $teamIds]]);

        $event->setData($teamMembers);
        $event->stopPropagation();
    }
}
