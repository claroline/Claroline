<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\User;

use JMS\DiExtraBundle\Annotation as DI;
use FOS\RestBundle\Controller\FOSRestController;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Form\GroupType;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Action\AdditionalAction;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Form\User\GroupSettingsType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Library\Security\Collection\GroupCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;

/**
 * @NamePrefix("api_")
 */
class GroupController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "groupManager" = @DI\Inject("claroline.manager.group_manager"),
     *     "roleManager"  = @DI\Inject("claroline.manager.role_manager"),
     *     "request"      = @DI\Inject("request"),
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "apiManager"   = @DI\Inject("claroline.manager.api_manager")
     * })
     */
    public function __construct(
        FormFactory   $formFactory,
        GroupManager  $groupManager,
        RoleManager   $roleManager,
        ObjectManager $om,
        Request       $request,
        ApiManager    $apiManager
    )
    {
        $this->formFactory     = $formFactory;
        $this->groupManager    = $groupManager;
        $this->roleManager     = $roleManager;
        $this->om              = $om;
        $this->groupRepository = $this->om->getRepository('ClarolineCoreBundle:Group');
        $this->request         = $request;
        $this->apiManager      = $apiManager;
    }

    /**
     * @View(serializerGroups={"api_group"})
     * @ApiDoc(
     *     description="Returns the groups list",
     *     views = {"group"}
     * )
     */
    public function getGroupsAction()
    {
        $this->throwsExceptionIfNotAdmin();

        return $this->groupRepository->findAll();
    }

    /**
     * @View(serializerGroups={"api_group"})
     * @ApiDoc(
     *     description="Returns a group",
     *     views = {"group"}
     * )
     */
    public function getGroupAction(Group $group)
    {
        $this->throwExceptionIfNotGranted('view', $group);

        return $group;
    }

    /**
     * @View(serializerGroups={"api_group"})
     * @ApiDoc(
     *     description="Create a group",
     *     views = {"group"},
     *     input="Claroline\CoreBundle\Form\GroupType"
     * )
     */
    public function postGroupAction()
    {
        $this->throwExceptionIfNotGranted('create', new Group());

        $groupType = new GroupSettingsType(null, true, 'cgfm');
        $groupType->enableApi();
        $form = $this->formFactory->create($groupType, new Group());
        $form->submit($this->request);
        $group = null;
        $httpCode = 400;

        if ($form->isValid()) {
            $group = $form->getData();
            $newRoles = $form['platformRoles']->getData();

            $this->groupManager->insertGroup($group);
            $this->groupManager->setPlatformRoles($group, $newRoles);
            $httpCode = 200;
        }

        $options = array(
            'http_code' => $httpCode,
            'extra_parameters' => $group,
            'serializer_group'=> "api_group"
        );


        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:User\createGroupForm.html.twig',
            $form,
            $options
        );
    }

    /**
     * @View(serializerGroups={"api_group"})
     * @ApiDoc(
     *     description="Update a group",
     *     views = {"group"},
     *     input="Claroline\CoreBundle\Form\GroupType"
     * )
     */
    public function putGroupAction(Group $group)
    {
        $this->throwExceptionIfNotGranted('edit', $group);

        $oldRoles = $group->getPlatformRoles();
        $groupType = new GroupSettingsType($oldRoles, true, 'egfm');
        $groupType->enableApi();
        $form = $this->formFactory->create($groupType, $group);
        $form->submit($this->request);
        $httpCode = 400;

        if ($form->isValid()) {
            $group = $form->getData();
            $newRoles = $form['platformRoles']->getData();
            $this->groupManager->setPlatformRoles($group, $newRoles);
            $this->groupManager->updateGroup($group, $oldRoles);
            $httpCode = 200;
        }

        $options = array(
            'http_code' => $httpCode,
            'extra_parameters' => $group,
            'form_view' => array('group' => $group),
            'serializer_group'=> "api_group"
        );


        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:User\editGroupForm.html.twig',
            $form,
            $options
        );
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Removes a group",
     *     views = {"group"},
     * )
     */
    public function deleteGroupAction(Group $group)
    {
        $this->throwExceptionIfNotGranted('delete', $group);
        $this->groupManager->deleteGroup($group);

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Removes a list of group",
     *     views = {"group"},
     * )
     */
    public function deleteGroupsAction()
    {
        $groups = $this->apiManager->getParameters('groupIds', 'Claroline\CoreBundle\Entity\Group');
        $this->throwExceptionIfNotGranted('delete', $groups);
        $this->container->get('claroline.persistence.object_manager')->startFlushSuite();

        foreach ($groups as $group) {
            $this->groupManager->deleteGroup($group);
        }

        $this->container->get('claroline.persistence.object_manager')->endFlushSuite();

        return array('success');
    }

    /**
     * @View(serializerGroups={"api_group"})
     * @ApiDoc(
     *     description="Add a role to a group",
     *     views = {"group"}
     * )
     */
    public function addGroupRoleAction(Group $group, Role $role)
    {
        $this->throwExceptionIfNotGranted('edit', $group);
        $this->roleManager->associateRole($group, $role, false);

        return $group;
    }

    /**
     * @View(serializerGroups={"api_group"})
     * @ApiDoc(
     *     description="Remove a role from a group",
     *     views = {"group"}
     * )
     */
    public function removeGroupRoleAction(Group $group, Role $role)
    {
        $this->throwExceptionIfNotGranted('edit', $group);
        $this->roleManager->dissociateRole($group, $role);

        return $group;
    }

    /**
     * @View(serializerGroups={"api_group"})
     * @ApiDoc(
     *     description="Returns the users list",
     *     views = {"user"}
     * )
     * @Get("/groups/page/{page}/limit/{limit}/search", name="get_search_groups", options={ "method_prefix" = false })
     */
    public function getSearchGroupsAction($page, $limit)
    {
        $data = $this->request->query->all();
        $groups = $this->groupManager->searchPartialList($data, $page, $limit);
        $count = $this->groupManager->searchPartialList($data, $page, $limit, true);

        return array('groups' => $groups, 'total' => $count);
    }

    /**
     * @ApiDoc(
     *     description="Returns the searchable user fields",
     *     views = {"user"}
     * )
     */
    public function getGroupSearchableFieldsAction()
    {
        $baseFields = Group::getSearchableFields();

        return $baseFields;
    }

    /**
     * @View(serializerGroups={"api_group"})
     * @ApiDoc(
     *     description="Returns the group creation form",
     *     views = {"location"}
     * )
     */
    public function getGroupCreateFormAction()
    {
        $formType = new GroupSettingsType(null, true, 'cgfm');
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:User\createGroupForm.html.twig', $form);
    }


    /**
     * @View(serializerGroups={"api_group"})
     * @ApiDoc(
     *     description="Returns the group edition form",
     *     views = {"location"}
     * )
     * @Get("/group/{group}/edit/form", options={ "method_prefix" = false })
     */
    public function getGroupEditFormAction(Group $group)
    {
        $this->throwExceptionIfNotGranted('edit', $group);
        $formType = new GroupSettingsType($group->getPlatformRole(), true, 'egfm');
        $formType->enableApi();
        $form = $this->createForm($formType, $group);
        $options = array('form_view' => array('group' => $group), 'serializer_group'=> "api_group");

        return $this->apiManager->handleFormView('ClarolineCoreBundle:API:User\editGroupForm.html.twig', $form, $options);
    }

    /**
     * @Post("/groups/{group}/import/members", name="group_members_import", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_user"})
     *
     * @param Group $group
     *
     * @return Response
     */
    public function importMembersAction(Group $group)
    {
        $this->throwsExceptionIfNotAdmin();

        return $this->groupManager->importMembers(file_get_contents($this->request->files->get('csv')), $group);
    }

    private function isAdmin()
    {
        return $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
    }

    private function throwsExceptionIfNotAdmin()
    {
        if (!$this->isAdmin()) throw new AccessDeniedException("This action can only be done by the administrator");
    }

    private function isGroupGranted($action, $object)
    {
        return $this->container->get('security.authorization_checker')->isGranted($action, $object);
    }

    private function throwExceptionIfNotGranted($action, $groups)
    {
        $collection = is_array($groups) ? new GroupCollection($groups): new GroupCollection(array($groups));
        $isGranted = $this->isGroupGranted($action, $collection);

        if (!$isGranted) {
            $groupList = '';

            foreach ($collection->getGroups() as $group) {
                $groupList .= "[{$group->getName()}]";
            }
            throw new AccessDeniedException("You can't do the action [{$action}] on the user list {$groupList}");
        }
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Returns the list of actions an admin can do on a group",
     *     views = {"groups"}
     * )
     */
    public function getGroupAdminActionsAction()
    {
        return $this->om->getRepository('Claroline\CoreBundle\Entity\Action\AdditionalAction')->findByType('admin_group_action');
    }
}
