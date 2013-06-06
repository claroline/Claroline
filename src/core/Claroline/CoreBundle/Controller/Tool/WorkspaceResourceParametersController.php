<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Claroline\CoreBundle\Controller\Tool\AbstractParametersController;

class WorkspaceResourceParametersController extends AbstractParametersController
{
    public function workspaceResourceTypesAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findAll();

        return $this->render(
            'ClarolineCoreBundle:Resource:config_resources_manager.html.twig',
            array(
                'resourceTypes' => $resourceTypes,
            )
        );
    }
}