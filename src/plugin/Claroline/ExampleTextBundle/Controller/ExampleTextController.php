<?php

namespace Claroline\ExampleTextBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExampleTextController extends Controller
{
    public function openAction($exampleTextInstanceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        //get the instance.
        $instance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($exampleTextInstanceId);
        //get the resource.
        $resource = $instance->getResource();
        //get the current workspace.
        //if you only have the workspace id => $em->getRepository('Claroline\CoreBundle\Workspace\AbstractWorkspace')->find(...);
        $workspace = $instance->getWorkspace();


        //get the text.
        return $this->render('ClarolineExampleTextBundle::open.html.twig', array('text' => $resource, 'workspace' => $workspace));
    }
}