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

use Innova\PathBundle\Entity\PathTemplate;


class PathTemplateController extends Controller 
{

    /**
     * @Route(
     *     "/path_templates",
     *     name = "innova_path_get_pathtemplates",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     */
    public function getPathTemplatesAction()
    {
        $em = $this->entityManager();
        
        $results = $em->getRepository('InnovaPathBundle:PathTemplate')->findAll();

        $pathtemplates = array();

        foreach ($results as $result) {
            $pathtemplates[$result->getId()] = json_decode($result->getStep());
        }

        return new JsonResponse($pathtemplates);
    }

    /**
    * @Route(
    *     "/path_template/add",
    *     name = "innova_path_add_pathtemplate",
    *     options = {"expose"=true}
    * )
    * @Method("POST")
    *
    */
    public function addPathTemplateAction()
    {

        $em = $this->entityManager();

        $user = "Arnaud";
        $content = json_decode($this->get('request')->getContent());

        $pathTemplate = New PathTemplate;
        $pathTemplate->setUser($user)
                ->setEditDate(new \DateTime())
                ->setStep(json_encode($content->step))
                ->setName($content->name)
                ->setDescription($content->description);

        $em->persist($pathTemplate);
        $em->flush();

        return New Response();
    }

    /**
    * @Route(
    *     "/path_template/delete/{id}",
    *     name = "innova_path_delete_path",
    *     options = {"expose"=true}
    * )
    * @Method("DELETE")
    *
    */
    public function deletePathTemplateAction(PathTemplate $pathTemplate)
    {
        $em = $this->entityManager();

        $em->remove($pathTemplate);
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
     

     