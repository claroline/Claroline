<?php

/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2013 Innovalangues
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category   Entity
 * @package    InnovaPathBundle
 * @subpackage PathBundle
 * @author     Innovalangues <contact@innovalangues.net>
 * @copyright  2013 Innovalangues
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    0.1
 * @link       http://innovalangues.net
 */
namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

// Controller dependencies
use Doctrine\ORM\EntityManagerInterface;

use Innova\PathBundle\Entity\PathTemplate;

/**
 * Class PathTemplateController
 *
 * @category   Controller
 * @package    Innova
 * @subpackage PathBundle
 * @author     Innovalangues <contact@innovalangues.net>
 * @copyright  2013 Innovalangues
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    0.1
 * @link       http://innovalangues.net
 * 
 * @Route(
 *      "",
 *      name = "innova_path_template",
 *      service="innova.path_template.controller"
 * )
 */
class PathTemplateController
{
    /**
     * Current entity manager for data persist
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    
    /**
     * Current request
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    
    /**
     * Class constructor
     * Inject needed dependencies
     * @param EntityManagerInterface   $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Inject current request into service
     * @param Request $request
     * @return \Innova\PathBundle\Controller\PathController
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    
        return $this;
    }
    
    /**
     * Get all templates
     * @return JsonResponse
     * 
     * @Route(
     *     "/path_templates",
     *     name = "innova_path_get_pathtemplates",
     *     options = {"expose"=true}
     * )
     * @Method("GET")
     */
    public function getAllAction()
    {
        $results = $this->entityManager->getRepository('InnovaPathBundle:PathTemplate')->findAll();

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
     * Create a new template
     * @return Response
     * 
     * @Route(
     *     "/path_template/add",
     *     name = "innova_path_add_pathtemplate",
     *     options = {"expose"=true}
     * )
     * @Method("POST")
     */
    public function addAction(Request $data)
    {
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

        $this->entityManager->persist($pathTemplate);
        $this->entityManager->flush();

        return new Response(
            $pathTemplate->getId()
        );
    }

    /**
     * Edit existing template
     * @return Response
     * @throws NotFoundHttpException
     * 
     * @Route(
     *     "/path_template/edit/{id}",
     *     name = "innova_path_edit_pathtemplate",
     *     options = {"expose"=true}
     * )
     * @Method("PUT")
     */
    public function editAction($id, Request $data) 
    {
        $pathTemplate = $this->entityManager->getRepository('InnovaPathBundle:PathTemplate')->find($id);
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
            
            $this->entityManager->persist($pathTemplate);
            $this->entityManager->flush();
        
            return new Response(
                $pathTemplate->getId()
            );
        }
        else {
            // Path template not found
            throw new NotFoundHttpException('The template does not exist');
        }
    }
    
    /**
     * Delete template from database
     * @return Response
     * 
     * @Route(
     *     "/path_template/delete/{id}",
     *     name = "innova_path_delete_pathtemplate",
     *     options = {"expose"=true}
     * )
     * @Method("DELETE")
     */
    public function deleteAction(PathTemplate $pathTemplate)
    {
        $this->entityManager->remove($pathTemplate);
        $this->entityManager->flush();

        return new Response('ok');
    }
}
