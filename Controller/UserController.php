<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\DiExtraBundle\Annotation as DI;


class UserController extends Controller
{

    private $userManager;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "userManager" = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct
    (
        UserManager $userManager,
        WorkspaceManager $workspaceManager
    )
    {
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route("/searchInWorkspace/{workspaceId}/{search}",
     *      name="claro_user_search_in_workspace",
     *      requirements={"workspaceId" = "\d+"}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:User:user_search_workspace_results.html.twig")
     *
     */
    public function userSearchInWorkspaceAction($workspaceId, $search)
    {
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);
        $users = $this->userManager->getAllUsersByWorkspaceAndName($workspace, $search, 1, 10);
        $usersArray = $this->userManager->toArrayForPicker($users);

        return new JsonResponse($usersArray);
    }
}
