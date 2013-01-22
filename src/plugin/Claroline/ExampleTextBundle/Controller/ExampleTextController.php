<?php

namespace Claroline\ExampleTextBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExampleTextController extends Controller
{
    public function openAction($exampleTextId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        //get the resource
        $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')->find($exampleTextId);
        //get the current workspace.
        //if you only have the workspace id =>
        //$em->getRepository('Claroline\CoreBundle\Workspace\AbstractWorkspace')->find(...);
        $workspace = $resource->getWorkspace();

        //get the text.
        return $this->render(
            'ClarolineExampleTextBundle::open.html.twig',
            array('resource' => $resource, 'workspace' => $workspace)
        );
    }
}