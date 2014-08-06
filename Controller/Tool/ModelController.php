<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\ModelManager;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Model\Model;
use Claroline\CoreBundle\Form\ModelType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;

class ModelController extends Controller
{
    private $workspaceManager;
    private $security;
    private $request;
    private $userManager;
    private $groupManager;
    private $modelManager;
    private $formFactory;
    private $router;

    /**
     * @DI\InjectParams({
     *     "router"       = @DI\Inject("router"),
     *     "security"     = @DI\Inject("security.context"),
     *     "userManager"  = @DI\Inject("claroline.manager.user_manager"),
     *     "groupManager" = @DI\Inject("claroline.manager.group_manager"),
     *     "modelManager" = @DI\Inject("claroline.manager.model_manager"),
     *     "formFactory"  = @DI\Inject("form.factory")
     * })
     */
    public function __construct(
        RouterInterface $router,
        SecurityContextInterface $security,
        Request $request,
        UserManager $userManager,
        GroupManager $groupManager,
        FormFactoryInterface $formFactory,
        ModelManager $modelManager
    )
    {
        $this->router       = $router;
        $this->security     = $security;
        $this->formFactory  = $formFactory;
        $this->request      = $request;
        $this->userManager  = $userManager;
        $this->groupManager = $groupManager;
        $this->modelManager = $modelManager;
        $this->formFactory  = $formFactory;
    }

    /**
     * @param Workspace $workspace
     *
     * @EXT\Route(
     *     "/workspace/{workspace}/model/index",
     *     name="claro_workspace_model_index"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters\model:index.html.twig")
     */
    public function indexAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $models = $this->modelManager->getByWorkspace($workspace);

        return array('workspace' => $workspace, 'models' => $models);
    }

    /**
     * @param Workspace $workspace
     *
     * @EXT\Route(
     *     "/workspace/{workspace}/model/form",
     *     name="claro_workspace_model_modal_form"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters\model:modelModalForm.html.twig")
     */
    public function showModelModalFormAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(new ModelType(), new Model());
        $action = $this->router->generate('claro_workspace_model_modal_create', array('workspace' => $workspace->getId()));

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'action' => $action
        );
    }

    /**
     * @param Workspace $workspace
     *
     * @EXT\Route(
     *     "/workspace/{workspace}/model/create",
     *     name="claro_workspace_model_modal_create"
     * )
     */
    public function createModelAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(new ModelType(), new Model());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $model = $this->modelManager->create($form->get('name')->getData(), $workspace);

            return new JsonResponse(
                array(
                    'name' => $model->getName(),
                    'id' => $model->getId()
                )
            );
        }

        $action = $this->router->generate('claro_workspace_model_modal_create', array('workspace' => $workspace->getId()));

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters\model:modelModalForm.html.twig',
            array(
                'form' => $form->createView(),
                'workspace' => $workspace,
                'action' => $action
            )
        );
    }

    /**
     * @param Workspace $workspace
     *
     * @EXT\Route(
     *     "/{model}/delete",
     *     name="claro_model_delete",
     *     options = {"expose"=true}
     * )
     */
    public function deleteModelAction(Model $model)
    {
        $workspace = $model->getWorkspace();
        $id = $model->getId();
        $this->modelManager->delete($model);

        return new JsonResponse(array('id' => $id));
    }

    public function renameModelFormAction(Model $model)
    {

    }

    public function renameModel(Model $model)
    {

    }

    /**
     * @param Model $model
     *
     * @EXT\Route(
     *     "/model/{model}/share/index",
     *     name="claro_workspace_model_share_index"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters\model:indexShare.html.twig")
     */
    public function shareAsModelIndex(Model $model)
    {
        $this->checkAccess($workspace);

        return array('workspace' => $workspace);
    }

    /**
     * @EXT\Route(
     *     "/workspace/share/users/page/{page}",
     *     name="ws_share_user_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/workspace/share/users/page/{page}/search/{search}",
     *     name="ws_share_user_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters\model:userShare.html.twig")
     *
     * Displays the list of users that the current user can send a message to,
     * optionally filtered by a search on first name and last name
     *
     * @param integer $page
     * @param string  $search
     *
     * @return Response
     */
    public function userListAction($page, $search)
    {
        $trimmedSearch = trim($search);

        if ($trimmedSearch === '') {
            $users = $this->userManager->getAllUsers($page, 10);
        } else {
            $users = $this->userManager
                ->getAllUsersBySearch($page, $trimmedSearch, 10);
        }

        return array('users' => $users, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/workspace/share/groups/page/{page}",
     *     name="ws_share_group_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/workspace/share/groups/page/{page}/search/{search}",
     *     name="ws_share_group_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters\model:groupShare.html.twig")
     *
     *
     * Displays the list of groups that the current user can send a message to,
     * optionally filtered by a search on group name
     *
     * @param integer $page
     * @param string  $search
     *
     * @return Response
     */
    public function groupListAction($page, $search)
    {
        $trimmedSearch = trim($search);

        if ($trimmedSearch === '') {
            $groups = $this->groupManager->getAllGroups($page, 10);
        } else {
            $groups = $this->groupManager
                ->getAllGroupsBySearch($page, $trimmedSearch, 10);
        }

        return array('groups' => $groups, 'search' => $search);
    }

    public function shareWithUsersAction()
    {

    }

    public function shareWithGroupsAction()
    {

    }

    public function removeModelSharing()
    {

    }

    private function checkAccess(Workspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
    }
} 