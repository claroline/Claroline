<?php

namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
     */
    public function getPathTemplatesAction()
    {
        $em = $this->entityManager();

        $results = $em->getRepository('InnovaPathBundle:PathTemplate')->findAll();

        $pathtemplates = array();

        foreach ($results as $result) {
            $template = new \stdClass();
            $template->id = $result->getId();
            $template->name = $result->getName();
            $template->description = $result->getDescription();
            $template->step = json_decode($result->getStep());

            $pathtemplates[] = $template;
        }

        return new JsonResponse($pathtemplates);
    }
    
    /**
     * @Route(
     *     "/path_template/add",
     *     name = "innova_path_add_pathtemplate",
     *     options = {"expose"=true}
     * )
     * 
     * @Method("POST")
     */
    public function addPathTemplateAction(Request $data)
    {
        $em = $this->entityManager();

        $pathTemplate = new PathTemplate;
        
        $name = $data->request->get('name');
        if (!empty($name))
            $pathTemplate->setName($name);
        
        $description = $data->request->get('description');
        if (!empty($description))
            $pathTemplate->setDescription($description);
        
        $step = $data->request->get('step');
        if (!empty($step))
            $pathTemplate->setStep($step);

        $em->persist($pathTemplate);
        $em->flush();

        return new Response(
            $pathTemplate->getId()
        );
    }

    /**
     * editPathTemplateAction function
     *
     * @Route(
     *     "/path_template/edit/{id}",
     *     name = "innova_path_edit_pathtemplate",
     *     options = {"expose"=true}
     * )
     * 
     * @Method("PUT")
     */
    public function editPathTemplateAction($id, Request $data) 
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');
        $pathTemplate = $manager->getRepository('InnovaPathBundle:PathTemplate')->find($id);
        
        if ($pathTemplate) {
            
            $name = $data->request->get('name');
            if (!empty($name))
                $pathTemplate->setName($name);
        
            $description = $data->request->get('description');
            if (!empty($description))
                $pathTemplate->setDescription($description);
        
            $step = $data->request->get('step');
            if (!empty($step))
                $pathTemplate->setStep($step);
            
            $manager->persist($pathTemplate);
            $manager->flush();
        
            return new Response(
                $pathTemplate->getId()
            );
        }
        else {
            // Path template not found
            throw $this->createNotFoundException('The template does not exist');
        }
    }
    
    /**
     * @Route(
     *     "/path_template/delete/{id}",
     *     name = "innova_path_delete_pathtemplate",
     *     options = {"expose"=true}
     * )
     * 
     * @Method("DELETE")
     */
    public function deletePathTemplateAction(PathTemplate $pathTemplate)
    {
        $em = $this->entityManager();
        $em->remove($pathTemplate);
        $em->flush();

        return New Response("ok");
    }

    public function entityManager()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $em = $this->getDoctrine()->getManager();

        return $em;
    }
}
