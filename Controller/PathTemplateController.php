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
     *
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

        $template = new \stdClass();
        $template->id = $pathTemplate->getId();
        $template->name = $pathTemplate->getName();
        $template->description = $pathTemplate->getDescription();
        $template->step = json_decode($pathTemplate->getStep());

        return New JsonResponse($template);
    }

    /**
    * @Route(
    *     "/path_template/delete/{id}",
    *     name = "innova_path_delete_pathtemplate",
    *     options = {"expose"=true}
    * )
    * @Method("DELETE")
    *
    */
    public function deletePathTemplateAction(PathTemplate $pathTemplate)
    {
        $em = $this->entityManager();

        $id = $pathTemplate->getId();

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
