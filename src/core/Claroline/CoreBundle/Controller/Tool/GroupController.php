<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Doctrine\ORM\EntityRepository;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GroupController extends Controller
{
    const ABSTRACT_WS_CLASS = 'Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace';
    const NUMBER_GROUP_PER_ITERATION = 25;

    /**
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
        $this->checkIfAdmin($workspace);
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
            'ClarolineCoreBundle:Tool:workspace\group_management\group_parameters.html.twig',
            array(
                'workspace' => $workspace,
                'group' => $group,
                'form' => $form->createView()
            )
        );
    }

    /**
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
            'ClarolineCoreBundle:Tool:workspace\group_management\unregistered_group_list_layout.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * Removes many groups from a workspace.
     * It uses a query string of groupIds as parameter (groupIds[]=1&groupIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    //TODO: change groupsId into ids
    public function removeGroupsAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkIfAdmin($workspace);
        $roles = $em->getRepository('ClarolineCoreBundle:Role')
            ->findByWorkspace($workspace);
        $params = $this->get('request')->query->all();

        if (isset($params['groupIds'])) {
            $this->checkRemoveManagerRoleIsValid($params['groupIds'], $workspace);
            foreach ($params['groupIds'] as $groupId) {
                $group = $em->find('Claroline\CoreBundle\Entity\Group', $groupId);

                if (null != $group) {
                    foreach ($roles as $role) {
                        $group->removeRole($role);
                    }
                }
            }
        }

        $em->flush();

        return new Response("success", 204);
    }

    /**
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
            ->unregisteredGroupsOfWorkspace($workspace, $offset, self::NUMBER_GROUP_PER_ITERATION);
        $groups = $this->paginatorToArray($paginatorGroups);
        $content = $this->renderView(
            'ClarolineCoreBundle:Tool:workspace\group_management\group.json.twig',
            array('groups' => $groups)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
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
            ->registeredGroupsOfWorkspace($workspaceId, $offset, self::NUMBER_GROUP_PER_ITERATION);
        $groups = $this->paginatorToArray($paginatorGroups);
        $content = $this->renderView(
            'ClarolineCoreBundle:Tool:workspace\group_management\group.json.twig',
            array('groups' => $groups)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Adds many groups to a workspace.
     * It uses a query string of groupIds as parameter (groupIds[]=1&groupIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    //TODO: change groupsId into ids
    public function addGroupsAction($workspaceId)
    {
        $params = $this->get('request')->query->all();
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $groups = array();

        if (isset($params['groupIds'])) {
            foreach ($params['groupIds'] as $groupId) {
                $group = $em->find('Claroline\CoreBundle\Entity\Group', $groupId);
                $groups[] = $group;
                $group->addRole(
                    $em->getRepository('ClarolineCoreBundle:Role')
                        ->findCollaboratorRole($workspace)
                );
                $em->flush();
            }
        }

        $content = $this->renderView(
            'ClarolineCoreBundle:Tool:workspace\group_management\group.json.twig',
            array('groups' => $groups)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
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
            ->searchUnregisteredGroupsOfWorkspace(
                $search,
                $workspace,
                $offset,
                self::NUMBER_GROUP_PER_ITERATION
            );
        $groups = $this->paginatorToArray($paginatorGroups);
        $content = $this->renderView(
            'ClarolineCoreBundle:Tool:workspace\group_management\group.json.twig',
            array('groups' => $groups)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
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
            ->searchRegisteredGroupsOfWorkspace(
                $search,
                $workspace,
                $offset,
                self::NUMBER_GROUP_PER_ITERATION
            );
        $groups = $this->paginatorToArray($paginatorGroups);
        $content = $this->renderView(
            'ClarolineCoreBundle:Tool:workspace\group_management\group.json.twig',
            array('groups' => $groups)
        );
        $response = new Response($content);
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
            $group = $em->find('Claroline\CoreBundle\Entity\Group', $groupId);

            if (null !== $group) {
                if ($group->hasRole($managerRole->getName())) {
                    $countRemovedManagers += count($group->getUsers());
                }
            }
        }

        $userManagers = $em->getRepository('Claroline\CoreBundle\Entity\User')
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
     * Checks if the current user is the admin of a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @throws AccessDeniedHttpException
     */
    private function checkIfAdmin($workspace)
    {
        $managerRoleName = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->findManagerRole($workspace)
            ->getName();

        if (!$this->get('security.context')->isGranted($managerRoleName)) {
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
