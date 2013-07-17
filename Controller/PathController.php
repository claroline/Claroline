<?php

namespace Innova\PathBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PathController extends Controller
{
    /**
     * @Route(
     *     "/workspace",
     *     name = "innova_path"
     * )
     *
     * @Template("InnovaPathBundle::desktopTool.html.twig")
     *
     *
     * @return Response
     */
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        //get the resource
        // $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($id);

        $em = $this->getDoctrine()->getManager();
        $paths = $em->getRepository('InnovaPathBundle:Path')->findAll();

        //get the text.
        // return array('_resource' => $resource);
        return array(
            'paths' => $paths,
        );
    }


    /**
     * @Route(
     *     "/workspace/{id}",
     *     name = "innova_path_open"
     * )
     *
     * @Template("InnovaPathBundle::desktopTool.html.twig")
     *
     * @param integer $id
     *
     * @return Response
     */
    public function openAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        //get the resource
        // $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($id);

        $em = $this->getDoctrine()->getManager();
        $paths = $em->getRepository('InnovaPathBundle:Path')->findAll();

        //get the text.
        // return array('_resource' => $resource);
        return array(
            'paths' => $paths,
        );
    }
}