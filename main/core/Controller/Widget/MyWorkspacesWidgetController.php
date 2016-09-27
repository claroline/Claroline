<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Widget;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MyWorkspacesWidgetController extends Controller
{
    private $tokenStorage;
    private $utils;
    private $workspaceManager;
    private $workspaceTagManager;

    /**
     * @DI\InjectParams({
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "utils"                  = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager"    = @DI\Inject("claroline.manager.workspace_tag_manager")
     * })
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Utilities $utils,
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
    }

    /**
     * @EXT\Route(
     *     "/workspaces/widget/{mode}",
     *     name="claro_display_workspaces_widget",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Widget:displayMyWorkspacesWidget.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Renders the workspaces list widget
     *
     * @return Response
     */
    public function displayMyWorkspacesWidgetAction($mode, User $user)
    {
        $workspaces = [];

        switch ($mode) {
            case 0:
                $token = $this->tokenStorage->getToken();
                $roles = $this->utils->getRoles($token);
                $datas = $this->workspaceTagManager->getDatasForWorkspaceListByUser($user, $roles);
                $workspaces = $datas['workspaces'];
                break;
            case 1:
                $workspaces = $this->workspaceManager->getFavouriteWorkspacesByUser($user);
                break;
        }

        return ['workspaces' => $workspaces, 'mode' => $mode];
    }
}
