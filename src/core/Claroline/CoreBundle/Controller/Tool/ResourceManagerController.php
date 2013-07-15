<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class ResourceManagerController extends Controller
{
    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/rights/form/role/{role}",
     *     name="claro_workspace_resource_rights_creation_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\resource_manager:resourceRightsCreation.html.twig")
     *
     * @param AbstractWorkspace $workspace
     * @param Role $role
     *
     * @return Response
     */
    public function workspaceResourceRightsCreationFormAction(
        AbstractWorkspace $workspace,
        Role $role
    )
    {
        $em = $this->get('doctrine.orm.entity_manager');
        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->findWorkspaceRoot($workspace);
        $config = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('resource' => $resource, 'role' => $role));
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        return array(
            'workspace' => $workspace,
            'configs' => array($config),
            'resourceTypes' => $resourceTypes,
            'resourceId' => $resource->getId(),
            'roleId' => $role->getId(),
            'tool' => $this->getResourceManagerTool()
        );
    }

    private function getResourceManagerTool()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneByName('resource_manager');
    }
}
