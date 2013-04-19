<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Tool\WorkspaceOrderedTool;
use Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Form\WorkspaceOrderToolEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class WorkspaceToolsParametersController extends Controller
{
    /**
     * @Route(
     *     "/{workspaceId}/tools",
     *     name="claro_workspace_tools_roles"
     * )
     * @Method("GET")
     */
    public function workspaceToolsRolesAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $wsRoles = $em->getRepository('ClarolineCoreBundle:Role')->findByWorkspace($workspace);
        $anonRole = $em->getRepository('ClarolineCoreBundle:Role')->findBy(array('name' => 'ROLE_ANONYMOUS'));
        $wsRoles = array_merge($wsRoles, $anonRole);
        $toolsPermissions = $this->getToolPermissions($workspace, $wsRoles);
        $undisplayedTools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findByWorkspace($workspace, false);

        //this is the missing part of the array
        $toFill = array();
        //default display_order if there is no WorkspaceOrderTool
        $nextDisplayOrder = 1;

        if (!empty($toolsPermissions)) {
            //the next display_order will be the incrementation of the last WorkspaceOrderTool display_order
            $nextDisplayOrder = count($toolsPermissions) + 1;
        }

        foreach ($undisplayedTools as $undisplayedTool) {
            if ($undisplayedTool->isDisplayableInWorkspace()) {
                $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
                    ->findOneBy(array('workspace' => $workspaceId, 'tool' => $undisplayedTool->getId()));

                //create a WorkspaceOrderedTool for each Tool that hasn't already one
                if ($wot === null) {
                    $wot = new WorkspaceOrderedTool();
                    $wot->setOrder($nextDisplayOrder++);
                    $wot->setTool($undisplayedTool);
                    $wot->setWorkspace($workspace);
                    $wot->setName(
                        $this->container->get('translator')->trans(
                            $undisplayedTool->getName(),
                            array(),
                            'tools'
                        )
                    );
                    $em->persist($wot);
                    $em->flush();
                } else {
                    continue;
                }

                foreach ($wsRoles as $role) {
                    $roleVisibility[$role->getId()] = false;
                }
                $toFill[] = array('tool' => $wot, 'visibility' => $roleVisibility);
            }
        }

        $toolsPermissions = $this->container->get('claroline.utilities.misc')->arrayFill($toolsPermissions, $toFill);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:tool_roles.html.twig',
            array(
                'roles' => $wsRoles,
                'workspace' => $workspace,
                'toolPermissions' => $toolsPermissions
            )
        );
    }

    /**
     * @Route(
     *     "/remove/tool/{toolId}/workspace/{workspaceId}/role/{roleId}",
     *     name="claro_tool_workspace_remove",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * Remove a tool from a role in a workspace.
     *
     * @param integer $toolId
     * @param integer $roleId
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function workspaceRemoveToolAction($toolId, $roleId, $workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findOneBy(array('workspace' => $workspaceId, 'tool' => $toolId));

        $wtr = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole')
            ->findOneBy(array('role' => $roleId, 'workspaceOrderedTool' => $wot));
        $em->remove($wtr);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/add/tool/{toolId}/position/{position}/workspace/{workspaceId}/role/{roleId}",
     *     name="claro_tool_workspace_add",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * Adds a tool to a role in a workspace.
     *
     * @param integer $toolId
     * @param integer $roleId
     * @param integer $workspaceId
     * @param integer $position
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function workspaceAddToolAction($toolId, $roleId, $workspaceId, $position)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $role = $em->getRepository('ClarolineCoreBundle:Role')->find($roleId);
        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findOneBy(array('workspace' => $workspaceId, 'tool' => $toolId));
        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);

        if ($wot === null) {
            $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);
            $wot = new WorkspaceOrderedTool();
            $wot->setOrder($position);
            $wot->setTool($tool);
            $wot->setWorkspace($workspace);
            $wot->setName($tool->getName());
            $em->persist($wot);
        }

        $wtr = new WorkspaceToolRole();
        $wtr->setRole($role);
        $wtr->setWorkspaceOrderedTool($wot);
        $em->persist($wtr);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/move/tool/{toolId}/position/{position}/workspace/{workspaceId}",
     *     name="claro_tool_workspace_move",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * This method switch the position of a tool with an other one.
     *
     * @param integer $toolId
     * @param integer $position
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceMoveToolAction($toolId, $position, $workspaceId)
    {
        if (intval($position) == null) {
            throw new \RuntimeException('The $position value must be an integer');
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);

        $movingTool = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
           ->findOneBy(array('tool' => $tool, 'workspace' => $workspace));

        if ($movingTool === null) {
            throw new \RuntimeException(
                "There is no WorkspaceOrderedTool for {$tool->getName()} in {$workspace->getName()}"
            );
        }

        $switchTool = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
           ->findOneBy(array('order' => $position, 'workspace' => $workspace));

         //if a tool is already at this position, he must go "far away"
        if ($switchTool !== null && $movingTool !== null) {
            //go far away ! Integrety constraints.
            $newPosition = $movingTool->getOrder();
            $switchTool->setOrder('99');
            $em->persist($switchTool);
        }

        $em->flush();

         //the tool must exists
        if ($movingTool !== null) {
            $movingTool->setOrder(intval($position));
            $em->persist($movingTool);
        }

        //put the original tool back.
        if ($switchTool !== null) {
            $switchTool->setOrder($newPosition);
            $em->persist($switchTool);
        }

        $em->flush();

        return new Response('success');
    }

        /**
     * @Route(
     *     "/{workspaceId}/tools/{workspaceOrderToolId}/editform",
     *     name="claro_workspace_order_tool_edit_form"
     * )
     * @Method("GET")
     *
     * @param integer $workspaceId
     * @param integer $workspaceOrderToolId
     *
     * @return Response
     */
    public function workspaceOrderToolEditFormAction($workspaceId, $workspaceOrderToolId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')->find($workspaceOrderToolId);

        $form = $this->createForm(new WorkspaceOrderToolEditType(), $wot);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:workspace_order_tool_edit.html.twig',
            array('form' => $form->createView(), 'workspace' => $workspace, 'wot' => $wot)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/tools/{workspaceOrderToolId}/edit",
     *     name="claro_workspace_order_tool_edit"
     * )
     * @Method("POST")
     *
     * @param integer $workspaceId
     * @param integer $workspaceOrderToolId
     *
     * @return Response
     */
    public function workspaceOrderToolEditAction($workspaceId, $workspaceOrderToolId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')->find($workspaceOrderToolId);

        $form = $this->createForm(new WorkspaceOrderToolEditType(), $wot);
        $request = $this->getRequest();
        $form->bind($request);

        if ($form->isValid()) {
            $em->persist($wot);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_tools_roles',
                    array('workspaceId' => $workspaceId)
                )
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:workspace_order_tool_edit.html.twig',
            array('form' => $form->createView(), 'workspace' => $workspace, 'wot' => $wot)
        );
    }

    /**
     * Creates an easy to use array with tools visibility permissons.
     * The array has the following structure:
     *
     * array[$order] => array(
     *  'tool' => $tool'
     *  'visibility' => array('
     *      ROLE_1 => $bool,
     *      ROLE_2 => $bool,
     *      ROLE_3 => $bool)
     * );
     */
    private function getToolPermissions($workspace, $wsRoles)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findBy(array('workspace' => $workspace->getId()), array('order' => 'ASC'));
        /* Loading all the datas from the WorkspaceToolRole entities
         * so doctrine won't do a new request every time the isToolVisibleForRoleInWorkspace()
         * is fired.
         */
        $wtr = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole')->findByWorkspace($workspace);

        foreach ($wot as $orderedTool) {

            if ($orderedTool->getTool()->isDisplayableInWorkspace()) {
                //creates the visibility array
                foreach ($wsRoles as $role) {
                    $isVisible = false;
                    //is the tool visible for a role in a workspace ?
                    foreach ($wtr as $workspaceToolRole) {
                        if ($workspaceToolRole->getRole() == $role
                            && $workspaceToolRole->getWorkspaceOrderedTool()->getTool() == $orderedTool->getTool()
                            && $workspaceToolRole->getWorkspaceOrderedTool()->getWorkspace() == $workspace) {
                            $isVisible = true;
                        }
                    }
                    $roleVisibility[$role->getId()] = $isVisible;
                }
                $toolsPermissions[] = array(
                    'tool' => $orderedTool,
                    'visibility' => $roleVisibility
                );
            }
        }

        return $toolsPermissions;
    }
}