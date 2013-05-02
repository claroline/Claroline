<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Doctrine\ORM\EntityRepository;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleSubscribeEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleUnsubscribeEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class GroupController extends Controller
{
    const ABSTRACT_WS_CLASS = 'ClarolineCoreBundle:Workspace\AbstractWorkspace';
    const NUMBER_GROUP_PER_ITERATION = 25;

    /**
     * @Route(
     *     "/{workspaceId}/tools/group/{groupId}",
     *     name="claro_workspace_tools_show_group_parameters",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$", "groupId"="^(?=.*[1-9].*$)\d*$"}
     * )
     *
     * @Route(
     *     "/{workspaceId}/group/{groupId}",
     *     name="claro_workspace_tools_edit_group_parameters",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$", "groupId"="^(?=.*[1-9].*$)\d*$" }
     * )
     * @Method({"POST", "GET"})
     *
     * Renders the group parameter page with its layout and
     * edit the group parameters for the selected workspace.
     *
     * @param integer $workspaceId the workspace id
     * @param integer $groupId the group id
     *
     * @return Response
     */
    public function groupParametersAction($workspaceId, $groupId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        $group = $em->getRepository('ClarolineCoreBundle:Group')
            ->find($groupId);
        $roleRepo = $em->getRepository('ClarolineCoreBundle:Role');
        $role = $roleRepo->findWorkspaceRole($group, $workspace);
        $defaultData = array('role' => $role);
        $form = $this->createFormBuilder($defaultData, array('translation_domain' => 'platform'))
            ->add(
                'role',
                'entity',
                array(
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'property' => 'translationKey',
                    'query_builder' => function (EntityRepository $er) use ($workspaceId) {
                        return $er->createQueryBuilder('wr')
                            ->select('role')
                            ->from('Claroline\CoreBundle\Entity\Role', 'role')
                            ->leftJoin('role.workspace', 'workspace')
                            ->where('workspace.id = :workspaceId')
                            ->andWhere("role.name != 'ROLE_ANONYMOUS'")
                            ->setParameter('workspaceId', $workspaceId);
                    }
                )
            )
            ->getForm();

        if ($this->getRequest()->getMethod() == 'POST') {
            $request = $this->getRequest();
            $parameters = $request->request->all();
            //cannot bind request: why ?
            $newRole = $em->getRepository('ClarolineCoreBundle:Role')
                ->find($parameters['form']['role']);

            //verifications: can we change his role.
            if ($newRole->getId() != $roleRepo->findManagerRole($workspace)->getId()) {
                $this->checkRemoveManagerRoleIsValid(array($group->getId()), $workspace);
            }

            $group->removeRole($role, false);
            $group->addRole($newRole);
            $em->persist($group);
            $em->flush();
            $route = $this->get('router')->generate(
                'claro_workspace_open_tool',
                array('workspaceId' => $workspaceId, 'toolName' => 'group_management')
            );

            return new RedirectResponse($route);
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\group_management:group_parameters.html.twig',
            array(
                'workspace' => $workspace,
                'group' => $group,
                'form' => $form->createView()
            )
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/groups/unregistered",
     *     name="claro_workspace_unregistered_groups_list",
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @Method("GET")
     *
     * Renders the unregistered group list layout for a workspace.
     *
     * @param integer $workspaceId workspace id
     *
     * @return Response
     */
    public function unregiseredGroupsListAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\group_management:unregistered_group_list_layout.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/groups",
     *     name="claro_workspace_delete_groups",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @Method("DELETE")
     *
     * Removes many groups from a workspace.
     * It uses a query string of groupIds as parameter (groupIds[]=1&groupIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function removeGroupsAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        $roles = $em->getRepository('ClarolineCoreBundle:Role')
            ->findByWorkspace($workspace);
        $params = $this->get('request')->query->all();

        $groups= array();
        $rolesForGroups = array();
        if (isset($params['ids'])) {
            $this->checkRemoveManagerRoleIsValid($params['ids'], $workspace);
            foreach ($params['ids'] as $groupId) {
                $group = $em->find('ClarolineCoreBundle:Group', $groupId);

                if (null != $group) {
                    $rolesForGroup = array();
                    foreach ($roles as $role) {
                        if ($group->hasRole($role->getName())) {
                            $group->removeRole($role);
                            $rolesForGroup[] = $role;
                        }
                    }
                    $groups[] = $group;
                    $rolesForGroups['group_'.$group->getId()] = $rolesForGroup;
                }
            }
        }

        foreach ($groups as $group) {
            foreach ($rolesForGroups['group_'.$group->getId()] as $role) {
                $log = new LogWorkspaceRoleUnsubscribeEvent($role, null, $group);
                $this->get('event_dispatcher')->dispatch('log', $log);
            }
        }

        $em->flush();

        return new Response("success", 204);
    }

    /**
     * @Route(
     *     "/{workspaceId}/groups/{offset}/unregistered",
     *     name="claro_workspace_unregistered_groups_paginated",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$", "offset"="^(?=.*[0-9].*$)\d*$"}
     * )
     * @Method("GET")
     *
     * Returns a partial json representation of the unregistered groups of a workspace.
     *
     * @param integer $workspaceId the workspace id
     * @param integer $offset      the offset
     *
     * @return Response
     */
    public function unregisteredGroupsAction($workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        $paginatorGroups = $em->getRepository('ClarolineCoreBundle:Group')
            ->findWorkspaceOutsider($workspace, $offset, self::NUMBER_GROUP_PER_ITERATION);
        $groups = $this->paginatorToArray($paginatorGroups);
        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeGroups($groups));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route(
     *     "/{workspaceId}/groups/{offset}/registered",
     *     name="claro_workspace_registered_groups_paginated",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="(?=.*[0-9].*$)\d*$", "offset"="(?=.*[0-9].*$)\d*$"}
     * )
     * @Method("GET")
     *
     * Returns a partial json representation of the registered groups of a workspace.
     *
     * @param integer $workspaceId the workspace id
     * @param integer $offset      the offset
     *
     * @return Response
     */
    public function registeredGroupsAction($workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        $paginatorGroups = $em->getRepository('ClarolineCoreBundle:Group')
            ->findByWorkspace($workspace, $offset, self::NUMBER_GROUP_PER_ITERATION);
        $groups = $this->paginatorToArray($paginatorGroups);
        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeGroups($groups));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route(
     *     "/{workspaceId}/add/group",
     *     name="claro_workspace_multiadd_group",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @Method("PUT")
     *
     * Adds many groups to a workspace.
     * It uses a query string of groupIds as parameter (groupIds[]=1&groupIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function addGroupsAction($workspaceId)
    {
        $params = $this->get('request')->query->all();
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        $role = $em->getRepository('ClarolineCoreBundle:Role')
                        ->findCollaboratorRole($workspace);
        $groups = array();

        if (isset($params['ids'])) {
            foreach ($params['ids'] as $groupId) {
                $group = $em->find('ClarolineCoreBundle:Group', $groupId);
                $groups[] = $group;
                $group->addRole($role);
                $em->flush();
            }
        }

        foreach ($groups as $group) {
            $log = new LogWorkspaceRoleSubscribeEvent($role, null, $group);
            $this->get('event_dispatcher')->dispatch('log', $log);
        }

        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeGroups($groups));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route(
     *     "/{workspaceId}/group/search/{search}/unregistered/{offset}",
     *     name="claro_workspace_search_unregistered_groups",
     *     requirements={"workspaceId"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Returns a partial json representation of the unregistered groups of a workspace.
     * It'll search every groups whose name match $search.
     *
     * @param string  $search      the search string
     * @param integer $workspaceId the workspace id
     * @param integer $offset      the offset
     *
     * @return Response
     */
    public function searchUnregisteredGroupsAction($search, $workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        $paginatorGroups = $em->getRepository('ClarolineCoreBundle:Group')
            ->findWorkspaceOutsiderByName(
                $workspace,
                $search,
                $offset,
                self::NUMBER_GROUP_PER_ITERATION
            );
        $groups = $this->paginatorToArray($paginatorGroups);
        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeGroups($groups));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route(
     *     "/{workspaceId}/group/search/{search}/registered/{offset}",
     *     name="claro_workspace_search_registered_groups",
     *     requirements={"workspaceId"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Returns a partial json representation of the registered groups of a workspace.
     * It'll search every groups whose name match $search.
     *
     * @param string  $search      the search string
     * @param integer $workspaceId the workspace id
     * @param integer $offset      the offset
     *
     * @return Response
     */
    public function searchRegisteredGroupsAction($search, $workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        $paginatorGroups = $em->getRepository('ClarolineCoreBundle:Group')
            ->findByWorkspaceAndName(
                $workspace,
                $search,
                $offset,
                self::NUMBER_GROUP_PER_ITERATION
            );
        $groups = $this->paginatorToArray($paginatorGroups);
        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeGroups($groups));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Checks if the role manager of the group can be changed.
     * There should be awlays at least one manager of a workspace.
     *
     * @param array $groupIds an array of group ids.
     * @param AbstractWorkspace $workspace the relevant workspace
     *
     * @throws LogicException
     */
    private function checkRemoveManagerRoleIsValid($groupIds, $workspace)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $managerRole = $em->getRepository('ClarolineCoreBundle:Role')
            ->findManagerRole($workspace);
        $countRemovedManagers = 0;

        foreach ($groupIds as $groupId) {
            $group = $em->find('ClarolineCoreBundle:Group', $groupId);

            if (null !== $group) {
                if ($group->hasRole($managerRole->getName())) {
                    $countRemovedManagers += count($group->getUsers());
                }
            }
        }

        $userManagers = $em->getRepository('ClarolineCoreBundle:User')
            ->findByWorkspaceAndRole($workspace, $managerRole);
        $countUserManagers = count($userManagers);

        if ($countRemovedManagers >= $countUserManagers) {
            throw new LogicException(
                "You can't remove every managers(you're trying to remove {$countRemovedManagers} "
                . "manager(s) out of {$countUserManagers})"
            );
        }
    }

    /**
     * Checks if the current user can see a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @throws AccessDeniedHttpException
     */
    private function checkRegistration($workspace)
    {
        if (!$this->get('security.context')->isGranted('group_management', $workspace)) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Most dql request required by this controller are paginated.
     * This function transform the results of the repository in an array.
     *
     * @param Paginator $paginator the return value of the Repository using a paginator.
     *
     * @return array.
     */
    private function paginatorToArray($paginator)
    {
        return $this->get('claroline.utilities.paginator_parser')
            ->paginatorToArray($paginator);
    }
}
