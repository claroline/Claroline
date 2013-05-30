<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Claroline\CoreBundle\Controller\Tool\AbstractParametersController;

class WorkspaceResourceParametersController extends AbstractParametersController
{
    /**
     * @Route(
     *     "/{workspaceId}/resource/rights/form",
     *     name="claro_workspace_resource_rights_form"
     * )
     * @Method("GET")
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceResourceRightsFormAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $this->checkAccess($workspace);
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->findWorkspaceRoot($workspace);
        $roleRights = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findNonAdminRights($resource);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:resources_rights.html.twig',
            array('workspace' => $workspace, 'resource' => $resource, 'roleRights' => $roleRights)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/resource/rights/form/role/{roleId}",
     *     name="claro_workspace_resource_rights_creation_form"
     * )
     * @Method("GET")
     *
     * @param integer $workspaceId
     * @param integer $roleId
     *
     * @return Response
     */
    public function workspaceResourceRightsCreationFormAction($workspaceId, $roleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $this->checkAccess($workspace);
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->findWorkspaceRoot($workspace);
        $role = $em->getRepository('ClarolineCoreBundle:Role')
            ->find($roleId);
        $config = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('resource' => $resource, 'role' => $role));
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:resource_rights_creation.html.twig',
            array(
                'workspace' => $workspace,
                'configs' => array($config),
                'resourceTypes' => $resourceTypes,
                'resourceId' => $resource->getId(),
                'roleId' => $roleId
            )
        );
    }
}