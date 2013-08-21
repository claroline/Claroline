<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\Response; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

use Innova\PathBundle\Entity\Path;


class PathController extends Controller 
{
    /**
     * @Route(
     *     "/",
     *     name = "innova_path_from_desktop",
     *     options = {"expose"=true}
     * )
     *
     * @Template("InnovaPathBundle::path_desktop.html.twig")
     */
    public function fromDesktopAction()
    {
        return array();
    }

    /**
     * @Route(
     *     "/",
     *     name = "innova_path_from_workspace",
     *     options = {"expose"=true}
     * )
     *
     * @Template("InnovaPathBundle::path_workspace.html.twig")
     *
     */
    public function fromWorkspaceAction()
    {
        
    }

    /**
     * @Route(
     *     "/paths",
     *     name = "innova_path_get_paths",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     */
    public function getPathsAction()
    {
        $em = $this->entityManager();
        
        $results = $em->getRepository('InnovaPathBundle:Path')->findAll();

        $paths = array();

        foreach ($results as $result) {
            $paths[$result->getId()] = json_decode($result->getPath());
        }

        return new JsonResponse($paths);
    }

    /**
     * @Route(
     *     "/path/{id}",
     *     name = "innova_path_get_path",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     */
    public function getPathAction(Path $path)
    {
        
        $json_path = array();
        $json_path[$path->getId()] = json_decode($path->getPath());
        
        return new JsonResponse($json_path);
    }

    /**
    * @Route(
    *     "/path/add",
    *     name = "innova_path_add_path",
    *     options = {"expose"=true}
    * )
    * @Method("POST")
    *
    */
    public function addPathAction()
    {

        $em = $this->entityManager();

        $editDate = new \DateTime();
        $user = "Arnaud";
        $content = $this->get('request')->getContent();
        
        $new_path = New Path;
        $new_path->setUser($user)
                ->setEditDate($editDate)
                ->setPath($content);

        $em->persist($new_path);
        $em->flush();

        return New Response();
    }

    /**
    * @Route(
    *     "/path/edit/{id}",
    *     name = "innova_path_edit_path",
    *     options = {"expose"=true}
    * )
    * @Method("PUT")
    *
    */
    public function editPathAction(Path $path)
    {
        $em = $this->entityManager();

        $editDate = new \DateTime();
        $content = $this->get('request')->getContent();

        $path->setEditDate($editDate);
        $path->setPath($content);

        $em->persist($path);
        $em->flush();

        return New Response();
    }

    /**
    * @Route(
    *     "/path/delete/{id}",
    *     name = "innova_path_delete_path",
    *     options = {"expose"=true}
    * )
    * @Method("DELETE")
    *
    */
    public function deletePathAction(Path $path)
    {
        $em = $this->entityManager();

        $em->remove($path);
        $em->flush();

        return New Response();
    }

    public function entityManager()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $em = $this->getDoctrine()->getManager();
        
        return $em;
    }

}
     

     