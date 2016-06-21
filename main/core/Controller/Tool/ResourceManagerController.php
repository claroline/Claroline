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

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class ResourceManagerController extends Controller
{
    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/rights/form/role/{role}",
     *     name="claro_workspace_resource_rights_creation_form"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\resource_manager:resourceRightsCreation.html.twig")
     *
     * @param Workspace $workspace
     * @param Role      $role
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function workspaceResourceRightsCreationFormAction(
        Workspace $workspace,
        Role $role
    ) {
        $token = $this->get('security.token_storage')->getToken();

        if (
            $this->get('claroline.manager.rights_manager')->canEditPwsPerm($token)
        ) {
            throw new AccessDeniedException();
        }

        $em = $this->get('doctrine.orm.entity_manager');

        if (!$this->get('security.authorization_checker')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $node = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
        $config = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('resourceNode' => $node, 'role' => $role));
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        return array(
            'workspace' => $workspace,
            'configs' => array($config),
            'resourceTypes' => $resourceTypes,
            'nodeId' => $node->getId(),
            'roleId' => $role->getId(),
            'tool' => $this->getResourceManagerTool(),
        );
    }

    private function getResourceManagerTool()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneByName('resource_manager');
    }
}
