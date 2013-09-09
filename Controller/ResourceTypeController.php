<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ResourceTypeController extends Controller{
    /**
     * @Template("ClarolineCoreBundle:Resource:configResourcesManager.html.twig")
     */
    public function initPickerAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findAll();

        return array('resourceTypes' => $resourceTypes);
    }
}