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
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\HomeTabManager;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Model\Model;
use Claroline\CoreBundle\Entity\Model\ResourceModel;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
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
    private $resourceManager;
    private $formFactory;
    private $router;
    private $homeTabManager;

    /**
     * @DI\InjectParams({
     *     "router"          = @DI\Inject("router"),
     *     "security"        = @DI\Inject("security.context"),
     *     "userManager"     = @DI\Inject("claroline.manager.user_manager"),
     *     "groupManager"    = @DI\Inject("claroline.manager.group_manager"),
     *     "modelManager"    = @DI\Inject("claroline.manager.model_manager"),
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager"),
     *     "homeTabManager"  = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "formFactory"     = @DI\Inject("form.factory")
     * })
     */
    public function __construct(
        RouterInterface $router,
        SecurityContextInterface $security,
        Request $request,
        UserManager $userManager,
        GroupManager $groupManager,
        FormFactoryInterface $formFactory,
        ModelManager $modelManager,
        HomeTabManager $homeTabManager,
        ResourceManager $resourceManager
    )
    {
        $this->router          = $router;
        $this->security        = $security;
        $this->formFactory     = $formFactory;
        $this->request         = $request;
        $this->userManager     = $userManager;
        $this->groupManager    = $groupManager;
        $this->modelManager    = $modelManager;
        $this->formFactory     = $formFactory;
        $this->homeTabManager  = $homeTabManager;
        $this->resourceManager = $resourceManager;
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
        $action = $this->router->generate('claro_workspace_model_create', array('workspace' => $workspace->getId()));

        return array(
            'form' => $form->createView(),
            'action' => $action,
            'title' => 'create_model'
        );
    }

    /**
     * @param Workspace $workspace
     *
     * @EXT\Route(
     *     "/workspace/{workspace}/model/create",
     *     name="claro_workspace_model_create"
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

        $action = $this->router->generate('claro_workspace_model_create', array('workspace' => $workspace->getId()));

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters\model:modelModalForm.html.twig',
            array(
                'form' => $form->createView(),
                'action' => $action,
                'title' => 'create_model'
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
        $this->checkAccess($model->getWorkspace());
        $workspace = $model->getWorkspace();
        $id = $model->getId();
        $this->modelManager->delete($model);

        return new JsonResponse(array('id' => $id));
    }

    /**
     * @param Workspace $workspace
     *
     * @EXT\Route(
     *     "/{model}/rename/form",
     *     name="claro_workspace_model_rename_form",
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters\model:modelModalForm.html.twig")
     */
    public function renameModelModalFormAction(Model $model)
    {
        $this->checkAccess($model->getWorkspace());
        $form = $this->formFactory->create(new ModelType(), $model);
        $action = $this->router->generate('claro_workspace_model_rename', array('model' => $model->getId()));

        return array('form' => $form->createView(), 'action' => $action, 'title' => 'rename');
    }

    /**
     * @param Workspace $workspace
     *
     * @EXT\Route(
     *     "/{model}/rename",
     *     name="claro_workspace_model_rename",
     *     options = {"expose"=true}
     * )
     */
    public function renameModelAction(Model $model)
    {
        $this->checkAccess($model->getWorkspace());
        $oldName = $model->getName();
        $form = $this->formFactory->create(new ModelType(), $model);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $model = $this->modelManager->edit($model, $form->get('name')->getData());

            return new JsonResponse(
                array(
                    'id' => $model->getId(),
                    'name' => $model->getName()
                )
            );
        }

        $action = $this->router->generate('claro_workspace_model_rename', array('model' => $model->getId()));

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters\model:modelModalForm.html.twig',
            array('form' => $form->createView(), 'action' => $action, 'title' => 'rename')
        );
    }

    /**
     * @param Workspace $workspace
     *
     * @EXT\Route(
     *     "/{model}/configure",
     *     name="claro_workspace_model_configure",
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters\model:configure.html.twig")
     */
    public function configureModelAction(Model $model)
    {
        $resourceModels = $model->getResourceModel();
        $copied = [];
        $links = [];

        foreach ($resourceModels as $resourceModel) {
            $resourceModel->isCopy() ? $copied[] = $resourceModel: $links[] = $resourceModel;
        }

        $root = $this->resourceManager->getWorkspaceRoot($model->getWorkspace());

        return array(
            'model'  => $model,
            'copied' => $copied,
            'links'  => $links,
            'rootId' => $root->getId()
        );
    }

    /**
     * @param Model $model
     *
     * @EXT\Route(
     *     "/model/{model}/share/index",
     *     name="claro_workspace_model_share_user_list"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters\model:indexShare.html.twig")
     */
    public function shareModelUserListAction(Model $model)
    {
        $this->checkAccess($model->getWorkspace());

        return array(
            'model' => $model,
            'workspace' => $model->getWorkspace()
        );
    }

    /**
     * @EXT\Route(
     *     "/{model}/workspace/share/users/page/{page}",
     *     name="ws_share_user_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/{model}/workspace/share/users/page/{page}/search/{search}",
     *     name="ws_share_user_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
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
    public function userListAction(Model $model, $page, $search)
    {
        $trimmedSearch = trim($search);

        if ($trimmedSearch === '') {
            $users = $this->userManager->getUsersNotSharingModel($model, $page, 10);
        } else {
            $users = $this->userManager->getUsersNotSharingModelBySearch($model, $trimmedSearch, $page, 10);
        }

        return array('users' => $users, 'search' => $search, 'model' => $model);
    }

    /**
     * @EXT\Route(
     *     "/{model}/workspace/share/groups/page/{page}",
     *     name="ws_share_group_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/{model}/workspace/share/groups/page/{page}/search/{search}",
     *     name="ws_share_group_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
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
    public function groupListAction(Model $model, $page, $search)
    {
        $trimmedSearch = trim($search);

        if ($trimmedSearch === '') {
            $groups = $this->groupManager->getUsersNotSharingModel($model, $page, 10);
        } else {
            $groups = $this->groupManager->getUsersNotSharingModelBySearch($model, $page, $trimmedSearch, 10);
        }

        return array(
            'groups' => $groups,
            'search' => $search,
            'model' => $model
        );
    }

    /**
     * @EXT\Route(
     *     "/{model}/share/users/add",
     *     name="ws_share_users_add",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter(
     *     "users",
     *     class="ClarolineCoreBundle:User",
     *     options={"multipleIds"=true, "name"="userIds"}
     * )
     */
    public function shareUsersAction(Model $model, array $users)
    {
        $this->checkAccess($model->getWorkspace());
        $this->modelManager->addUsers($model, $users);
        $data = [];

        foreach ($users as $user) {
            $data['users'][] = array('id' => $user->getId(), 'username' => $user->getUsername());
        }

        $data['model']['id'] = $model->getId();

        return new JsonResponse($data);
    }

    /**
     * @EXT\Route(
     *     "/{model}/share/groups/add",
     *     name="ws_share_groups_add",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter(
     *     "groups",
     *     class="ClarolineCoreBundle:Group",
     *     options={"multipleIds"=true, "name"="groupIds"}
     * )
     */
    public function shareGroupsAction(Model $model, array $groups)
    {
        $this->checkAccess($model->getWorkspace());
        $this->modelManager->addGroups($model, $groups);

        foreach ($groups as $group) {
            $data['groups'][] = array('id' => $group->getId(), 'name' => $group->getName());
        }

        $data['model']['id'] = $model->getId();

        return new JsonResponse($data);
    }

    /**
     * @EXT\Route(
     *     "/{model}/group/{group}/remove",
     *     name="ws_model_remove_group",
     *     options={"expose"=true}
     * )
     */
    public function removeModeGroupAction(Model $model, Group $group)
    {
        $this->checkAccess($model->getWorkspace());
        $this->modelManager->removeGroup($model, $group);

        return new JsonResponse(array('id' => $group->getId()));
    }

    /**
     * @EXT\Route(
     *     "/{model}/user/{user}/remove",
     *     name="ws_model_remove_user",
     *     options={"expose"=true}
     * )
     */
    public function removeModelUserAction(Model $model, User $user)
    {
        $this->checkAccess($model->getWorkspace());
        $this->modelManager->removeUser($model, $user);

        return new JsonResponse(array('id' => $user->getId()));
    }

    /**
     * @EXT\Route(
     *     "/{model}/resource/copy/add",
     *     name="ws_model_resource_copy_add",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter(
     *     "resourceNodes",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"multipleIds"=true, "name"="nodeIds"}
     * )
     */
    public function addNodesCopyAction(Model $model, array $resourceNodes)
    {
        $this->checkAccess($model->getWorkspace());
        $resourceModels = $this->modelManager->addResourceNodes($model, $resourceNodes, true);
        $data = [];

        foreach ($resourceModels as $resourceModel) {
            $data[] = array(
                'resourceModelId' => $resourceModel->getId(),
                'name' => $resourceModel->getResourceNode()->getName()
            );
        }

        return new JsonResponse($data);
    }

    /**
     * @EXT\Route(
     *     "/{model}/resource/link/add",
     *     name="ws_model_resource_link_add",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter(
     *     "resourceNodes",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"multipleIds"=true, "name"="nodeIds"}
     * )
     */
    public function addNodeLinkAction(Model $model, array $resourceNodes)
    {
        $this->checkAccess($model->getWorkspace());
        $resourceModels = $this->modelManager->addResourceNodes($model, $resourceNodes, false);
        $data = [];

        foreach ($resourceModels as $resourceModel) {
            $data[] = array(
                'resourceModelId' => $resourceModel->getId(),
                'name' => $resourceModel->getResourceNode()->getName()
            );
        }

        return new JsonResponse($data);
    }

    /**
     * @EXT\Route(
     *     "/remove/resource/{resourceModel}",
     *     name="ws_model_resource_remove",
     *     options={"expose"=true}
     * )
     *
     * @param ResourceModel $resourceModel
     * @return JsonResponse
     */
    public function removeResourceModelAction(ResourceModel $resourceModel)
    {
        $this->checkAccess($resourceModel->getModel()->getWorkspace());
        $this->modelManager->removeResourceModel($resourceModel);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{model}/homeTabs/list",
     *     name="ws_model_homeTabs_list",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters\model:homeTabsList.html.twig")
     * @param Model $model
     */
    public function listHomeTabsAction(Model $model)
    {
        $this->checkAccess($model->getWorkspace());
        $homeTabsConfig = $this->homeTabManager->getWorkspaceHomeTabConfigsByWorkspace($model->getWorkspace());

        return array('homeTabsConfig' => $homeTabsConfig);
    }

    private function checkAccess(Workspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
    }
} 