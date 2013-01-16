<?php

namespace Claroline\CoreBundle\Controller;

use Doctrine\ORM\EntityRepository;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class WorkspaceGroupController extends Controller
{
    const ABSTRACT_WS_CLASS = 'Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace';
    const NUMBER_GROUP_PER_ITERATION = 25;
    /**
     * Renders the groups management page with its layout
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function groupsManagementAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);

        return $this->render('ClarolineCoreBundle:Workspace:tools\group_management.html.twig', array(
                'workspace' => $workspace)
        );
    }

    /**
     * Renders the group parameter page with its layout and
     * edit the group parameters for the selected workspace.
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function groupParametersAction($workspaceId, $groupId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkIfAdmin($workspace);
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($groupId);
        $role = $em->getRepository('ClarolineCoreBundle:Role')->getEntityRoleForWorkspace($group, $workspace);
        $defaultData = array('role' => $role);
        $form = $this->createFormBuilder($defaultData, array('translation_domain' => 'platform'))
            ->add(
                'role', 'entity', array(
                'class' => 'Claroline\CoreBundle\Entity\Role',
                'property' => 'translationKey',
                'query_builder' => function(EntityRepository $er) use ($workspaceId) {
                    return $er->createQueryBuilder('wr')
                        ->select('role')
                        ->from('Claroline\CoreBundle\Entity\Role', 'role')
                        ->leftJoin('role.workspaceRights', 'rights')
                        ->leftJoin('rights.workspace', 'workspace')
                        ->where('workspace.id = :workspaceId')
                        ->andWhere("role.name != 'ROLE_ANONYMOUS'")
                        ->setParameter('workspaceId', $workspaceId);
                }
            ))
            ->getForm();

        if ($this->getRequest()->getMethod() == 'POST') {
            $request = $this->getRequest();
            $parameters = $request->request->all();
            //cannot bind request: why ?
            $newRole = $em->getRepository('ClarolineCoreBundle:Role')->find($parameters['form']['role']);

            //verifications: his role cannot be changed
            if ($newRole->getId() != $em->getRepository('ClarolineCoreBundle:Role')->getManagerRole($workspace)->getId()){
                $groupIds = array($group->getId());
                $parameters['groupIds'] = $groupIds;
                $this->checkRemoveManagerRoleIsValid($parameters, $workspace);
            }

            $group->removeRole($role, false);
            $group->addRole($newRole);
            $em->persist($group);
            $em->flush();
            $route = $this->get('router')->generate('claro_workspace_tools_groups_management', array('workspaceId' => $workspaceId));

            return new RedirectResponse($route);
        }

        return $this->render('ClarolineCoreBundle:Workspace:tools\group_parameters.html.twig', array(
                'workspace' => $workspace, 'group' => $group, 'form' => $form->createView())
        );
    }

    /**
     * Renders the unregistered group list layout for a workspace.
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function unregiseredGroupsListAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);

       return $this->render('ClarolineCoreBundle:Workspace:tools\unregistered_group_list_layout.html.twig', array(
                'workspace' => $workspace)
       );
    }

    /**
     * Removes many groups from a workspace. ( ?0=1&1=2... )
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function removeGroupsAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkIfAdmin($workspace);
        $roles = $em->getRepository('ClarolineCoreBundle:Role')->getWorkspaceRoles($workspace);
        $params = $this->get('request')->query->all();
        $this->checkRemoveManagerRoleIsValid($params, $workspace);

        if(isset($params['groupIds'])){
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
     * Renders a list of unregistered groups for a workspace
     * if page = 1, it'll render groups 1-10
     * if page = 2, it'll render groups 11-20
     * if page = 3, it'll render groups 21-30
     * ...
     *
     * @param integer $workspaceId
     * @param integer $page
     *
     * @return Response
     */
    public function unregisteredGroupsAction($workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);
        $paginatorGroups = $em->getRepository('ClarolineCoreBundle:Group')->unregisteredGroupsOfWorkspace($workspace, $offset, self::NUMBER_GROUP_PER_ITERATION);
        $groups = $this->paginatorToArray($paginatorGroups);
        $content = $this->renderView("ClarolineCoreBundle:Workspace:group.json.twig", array('groups' => $groups));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Renders a list of registered groups for a workspace
     *
     * @param integer $workspaceId
     * @param integer $page
     *
     * @return Response
     */
    public function registeredGroupsAction($workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);
        $paginatorGroups = $em->getRepository('ClarolineCoreBundle:Group')->registeredGroupsOfWorkspace($workspaceId, $offset, self::NUMBER_GROUP_PER_ITERATION);
        $groups = $this->paginatorToArray($paginatorGroups);
        $content = $this->renderView("ClarolineCoreBundle:Workspace:group.json.twig", array('groups' => $groups));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    /**
     * Adds many groups to a workspace.
     * It should be used with ajax and a list of grouppIds as parameter.
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function addGroupsAction($workspaceId)
    {
        $params = $this->get('request')->query->all();
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $groups = array();

        if (isset($params['groupIds'])){
            foreach ($params['groupIds'] as $groupId) {
                 $group = $em->find('Claroline\CoreBundle\Entity\Group', $groupId);
                 $groups[] = $group;
                 $group->addRole($em->getRepository('ClarolineCoreBundle:Role')->getCollaboratorRole($workspace));
                 $em->flush();
            }
        }

        $content = $this->renderView('ClarolineCoreBundle:Workspace:group.json.twig', array('groups' => $groups));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Renders a list of unregistered groups for a workspace.
     * It'll search every groups whose name match $search.
     *
     * @param string $search
     * @param integer $workspaceId
     * @param string $format
     *
     * @return Response
     */
    public function searchUnregisteredGroupsAction($search, $workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);
        $paginatorGroups = $em->getRepository('ClarolineCoreBundle:Group')->searchUnregisteredGroupsOfWorkspace($search, $workspace, $offset, self::NUMBER_GROUP_PER_ITERATION);
        $groups = $this->paginatorToArray($paginatorGroups);
        $content = $this->renderView("ClarolineCoreBundle:Workspace:group.json.twig", array('groups' => $groups));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Renders a list of registered groups for a workspace.
     * It'll search every groups whose name match $search.
     *
     * @param string $search
     * @param integer $workspaceId
     * @param string $format
     *
     * @return Response
     */
    public function searchRegisteredGroupsAction($search, $workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);
        $paginatorGroups = $em->getRepository('ClarolineCoreBundle:Group')->searchRegisteredGroupsOfWorkspace($search, $workspace, $offset, self::NUMBER_GROUP_PER_ITERATION);
        $groups = $this->paginatorToArray($paginatorGroups);
        $content = $this->renderView("ClarolineCoreBundle:Workspace:group.json.twig", array('groups' => $groups));
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /*******************/
    /* PRIVATE METHODS */
    /*******************/

    private function checkRemoveManagerRoleIsValid($parameters, $workspace)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $countRemovedManagers = 0;

        if(isset($parameters['groupIds'])){
            foreach ($parameters['groupIds'] as $groupId) {
                $group = $em->find('Claroline\CoreBundle\Entity\Group', $groupId);

                if (null !== $group){
                    if ($group->hasRole($em->getRepository('ClarolineCoreBundle:Role')->getManagerRole($workspace)->getName())) {
                        $countRemovedManagers += count($group->getUsers());
                    }
                }
            }
        }

        $userManagers = $em->getRepository('Claroline\CoreBundle\Entity\User')->getUsersOfWorkspace($workspace, $em->getRepository('ClarolineCoreBundle:Role')->getManagerRole($workspace), true);
        $countUserManagers = count($userManagers);

        if ($countRemovedManagers >= $countUserManagers) {
            throw new LogicException("You can't remove every managers(you're trying to remove {$countRemovedManagers} manager(s) out of {$countUserManagers})");
        }
    }

    private function checkRegistration($workspace)
    {
        if(!$this->get('security.context')->isGranted('VIEW', $workspace))
        {
            throw new AccessDeniedHttpException();
        }
    }

    private function checkIfAdmin($workspace)
    {
        if (!$this->get('security.context')->isGranted($this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Role')->getManagerRole($workspace)->getName())) {
            throw new AccessDeniedHttpException();
        }
    }

    private function paginatorToArray($paginator)
    {

        $items = array();

        foreach($paginator as $item){
            $items[] = $item;
        }

        return $items;
    }
}


