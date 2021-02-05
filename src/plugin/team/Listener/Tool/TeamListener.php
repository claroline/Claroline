<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Listener\Tool;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\TeamBundle\Entity\Team;
use Claroline\TeamBundle\Manager\TeamManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TeamListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TeamManager */
    private $teamManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        SerializerProvider $serializer,
        TeamManager $teamManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorization = $authorization;
        $this->serializer = $serializer;
        $this->teamManager = $teamManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function onWorkspaceToolOpen(OpenToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $teamParams = $this->teamManager->getWorkspaceTeamParameters($workspace);
        $canEdit = $this->authorization->isGranted(['claroline_team_tool', 'edit'], $workspace);
        /** @var string|User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $myTeams = 'anon.' !== $user ?
            $this->teamManager->getTeamsByUserAndWorkspace($user, $workspace) :
            [];
        $event->setData([
            'teamParams' => $this->serializer->serialize($teamParams),
            'canEdit' => $canEdit,
            'myTeams' => array_map(function (Team $team) {
                return $team->getUuid();
            }, $myTeams),
        ]);
        $event->stopPropagation();
    }
}
