<?php

namespace Claroline\ExampleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExampleController extends Controller
{
    public function openAction($exampleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        //get the resource
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($exampleId);
        //get the current workspace.
        //if you only have the workspace id =>
        //$em->getRepository('Claroline\CoreBundle\Workspace\AbstractWorkspace')->find(...);
        $workspace = $resource->getWorkspace();

        //get the text.
        return $this->render(
            'ClarolineExampleBundle::resource.html.twig',
            array('resource' => $resource, 'workspace' => $workspace)
        );
    }
}