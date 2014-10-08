<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TeamBundle\Manager\TeamManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeamController extends Controller
{
    private $httpKernel;
    private $request;
    private $securityContext;
    private $teamManager;

    /**
     * @DI\InjectParams({
     *     "httpKernel"      = @DI\Inject("http_kernel"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "securityContext" = @DI\Inject("security.context"),
     *     "teamManager"     = @DI\Inject("claroline.manager.team_manager")
     * })
     */
    public function __construct(
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        SecurityContextInterface $securityContext,
        TeamManager $teamManager
    )
    {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->securityContext = $securityContext;
        $this->teamManager = $teamManager;
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/team/index",
     *     name="claro_team_index"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param Workspace $workspace
     * @param User $user
     */
    public function indexAction(Workspace $workspace, User $user)
    {
        $isWorkspaceManager = $this->isWorkspaceManager($workspace, $user);
        $params = array();

        if ($isWorkspaceManager) {
            $params['_controller'] = 'ClarolineTeamBundle:Team:managerMenu';
            $params['workspace'] = $workspace->getId();
        } else {
            $params['_controller'] = 'ClarolineTeamBundle:Team:userMenu';
            $params['workspace'] = $workspace->getId();
        }
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/team/manager/menu/ordered/by/{orderedBy}/order/{$order}",
     *     name="claro_team_manager_menu",
     *     defaults={"orderedBy"="name","order"="ASC"}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Workspace $workspace
     * @param User $user
     */
    public function managerMenuAction(
        Workspace $workspace,
        User $user,
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        $this->checkWorkspaceManager($workspace, $user);

        $teams = $this->teamManager
            ->getTeamsByWorkspace($workspace, $orderedBy, $order);

        return array(
            'workspace' => $workspace,
            'user' => $user,
            'teams' => $teams
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/team/user/menu",
     *     name="claro_team_user_menu",
     *     defaults={"orderedBy"="name","order"="ASC"}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Workspace $workspace
     * @param User $user
     */
    public function userMenuAction(
        Workspace $workspace,
        User $user,
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        $this->checkToolAccess($workspace);

        $teams = $this->teamManager
            ->getTeamsByWorkspace($workspace, $orderedBy, $order);

        return array(
            'workspace' => $workspace,
            'user' => $user,
            'teams' => $teams
        );
    }

    private function checkToolAccess(Workspace $workspace)
    {
        if (!$this->securityContext->isGranted('claroline_team_tool', $workspace)) {

            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceManager(Workspace $workspace, User $user)
    {
        if (!$this->isWorkspaceManager($workspace, $user)) {

            throw new AccessDeniedException();
        }
    }

    private function isWorkspaceManager(Workspace $workspace, User $user)
    {
        $isWorkspaceManager = false;
        $managerRole = 'ROLE_WS_MANAGER_' . $workspace->getGuid();
        $roleNames = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roleNames) ||
            in_array($managerRole, $roleNames)) {

            $isWorkspaceManager = true;
        }

        return $isWorkspaceManager;
    }
}
