<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Listener;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\TeamBundle\Entity\Team;
use Claroline\TeamBundle\Manager\TeamManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service
 */
class TeamListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TeamManager */
    private $teamManager;
    /** @var TokenStorage */
    private $tokenStorage;

    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;

    /**
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer"),
     *     "teamManager"   = @DI\Inject("claroline.manager.team_manager"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param SerializerProvider            $serializer
     * @param TeamManager                   $teamManager
     * @param TokenStorage                  $tokenStorage
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        SerializerProvider $serializer,
        TeamManager $teamManager,
        TokenStorage $tokenStorage
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->teamManager = $teamManager;
        $this->tokenStorage = $tokenStorage;

        $this->resourceTypeRepo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType');
    }

    /**
     * @DI\Observe("open_tool_workspace_claroline_team_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onWorkspaceToolOpen(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $teamParams = $this->teamManager->getWorkspaceTeamParameters($workspace);
        $canEdit = $this->authorization->isGranted(['claroline_team_tool', 'edit'], $workspace);
        $resouceTypes = $this->resourceTypeRepo->findBy(['isEnabled' => true]);
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
            'resourceTypes' => array_map(function (ResourceType $resourceType) {
                return $resourceType->getName();
            }, $resouceTypes),
        ]);
        $event->stopPropagation();
    }
}
