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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    private $facetManager;
    private $userManager;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "facetManager"     = @DI\Inject("claroline.manager.facet_manager"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        FacetManager $facetManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager
    )
    {
        $this->facetManager = $facetManager;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route("/searchInWorkspace/{workspaceId}/{search}",
     *      name="claro_user_search_in_workspace",
     *      options = {"expose"=true},
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

    /**
     * @EXT\Route(
     *     "user/picker",
     *     name="claro_user_picker",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function userPickerAction(User $authenticatedUser)
    {
        $preferences = $this->facetManager->getVisiblePublicPreference();
        $withMail = $preferences['mail'];
        $users = $this->userManager->getUsersForUserPicker(
            $authenticatedUser,
            '',
            $withMail,
            1,
            50,
            'lastName',
            'ASC'
        );

        return array(
            'users' => $users,
            'search' => '',
            'withMail' => $withMail,
            'page' => 1,
            'max' => 50,
            'orderedBy' => 'lastName',
            'order' => 'ASC'
        );
    }

    /**
     * @EXT\Route(
     *     "users/list/for/user/picker/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_users_list_for_user_picker",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function usersListForUserPickerAction(
        User $authenticatedUser,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC'
    )
    {
        $preferences = $this->facetManager->getVisiblePublicPreference();
        $withMail = $preferences['mail'];
        $users = $this->userManager->getUsersForUserPicker(
            $authenticatedUser,
            $search,
            $withMail,
            $page,
            $max,
            $orderedBy,
            $order
        );

        return array(
            'users' => $users,
            'search' => $search,
            'withMail' => $withMail,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }
}
