<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API;

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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class GroupController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "groupManager" = @DI\Inject("claroline.manager.group_manager"),
     *     "roleManager"  = @DI\Inject("claroline.manager.role_manager"),
     *     "request"      = @DI\Inject("request"),
     *     "om"           = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactory   $formFactory,
        GroupManager  $groupManager,
        RoleManager   $roleManager,
        ObjectManager $om,
        Request       $request
    )
    {
        $this->formFactory = $formFactory;
        $this->groupManager = $groupManager;
        $this->roleManager = $roleManager;
        $this->om = $om;
        $this->groupRepository = $this->om->getRepository('ClarolineCoreBundle:Group');
        $this->request = $request;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the groups list",
     *     views = {"group"}
     * )
     */
    public function getGroupsAction()
    {
        return $this->groupRepository->findAll();
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns a group",
     *     views = {"group"}
     * )
     */
    public function getGroupAction(Group $group)
    {
        return $group;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Create a group",
     *     views = {"group"},
     *     input="Claroline\CoreBundle\Form\GroupType"
     * )
     */
    public function postGroupAction()
    {
        $groupType = new GroupType();
        $groupType->enableApi();
        $form = $this->formFactory->create($groupType, new Group());
        $form->submit($this->request);
        //$form->handleRequest($this->request);

        if ($form->isValid()) {
            $group = $form->getData();
            $userRole = $this->roleManager->getRoleByName('ROLE_USER');
            $group->setPlatformRole($userRole);
            $this->groupManager->insertGroup($group);

            return $group;
        }

        return $form;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Update a group",
     *     views = {"group"},
     *     input="Claroline\CoreBundle\Form\GroupType"
     * )
     */
    public function putGroupAction(Group $group)
    {
        $groupType = new GroupType();
        $groupType->enableApi();
        $form = $this->formFactory->create($groupType, $group);
        $form->submit($this->request);
        //$form->handleRequest($this->request);

        if ($form->isValid()) {
            $group = $form->getData();
            $userRole = $this->roleManager->getRoleByName('ROLE_USER');
            $this->groupManager->insertGroup($group);

            return $group;
        }

        return $form;
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
        $this->groupManager->deleteGroup($user);

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Add a role to a group",
     *     views = {"group"}
     * )
     */
    public function addGroupRoleAction(Group $group, Role $role)
    {
        $this->roleManager->associateRole($group, $role, false);

        return array('success');
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Remove a role from a group",
     *     views = {"group"}
     * )
     */
    public function removeGroupRoleAction(Group $group, Role $role)
    {
        $this->roleManager->dissociateRole($group, $role);

        return array('success');
    }
}
