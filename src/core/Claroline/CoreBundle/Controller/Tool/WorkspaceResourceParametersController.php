<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Claroline\CoreBundle\Controller\Tool\AbstractParametersController;

class WorkspaceResourceParametersController extends AbstractParametersController
{
    /**
     * @Template("ClarolineCoreBundle:Resource:configResourcesManager.html.twig")
     */
    public function workspaceResourceTypesAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findAll();

        return array('resourceTypes' => $resourceTypes);
    }
}
