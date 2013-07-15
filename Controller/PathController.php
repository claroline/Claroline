<?php

namespace Innova\PathBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PathController extends Controller
{
    /**
     * @Route(
     *     "/workspace/{id}",
     *     name = "innova_path"
     * )
     *
     * @Template("InnovaPathBundle::resource.html.twig")
     *
     * @param integer $id
     *
     * @return Response
     */
    public function openAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        //get the resource
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($id);

        //get the text.
        return array('_resource' => $resource);
    }
}