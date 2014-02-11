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

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

// Controller dependencies
use Innova\PathBundle\Manager\PathTemplateManager;
use Innova\PathBundle\Entity\Path\PathTemplate;
use Innova\PathBundle\Form\Handler\PathTemplateHandler;

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
 *      "/templates",
 *      name    = "innova_path_template",
 *      service = "innova_path.controller.path_template"
 * )
 */
class PathTemplateController
{
    /**
     * Form factory
     * @var \Symfony\Component\Form\FormFactoryInterface $formFactory
     */
    protected $formFactory;
    
    /**
     * Path template manager
     * @var \Innova\PathBundle\Manager\PathTemplateManager
     */
    protected $pathTemplateManager;

    /**
     * Class constructor
     * Inject needed dependencies
     * @param \Symfony\Component\Form\FormFactoryInterface   $formFactory
     * @param \Innova\PathBundle\Manager\PathTemplateManager $pathTemplateManager
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        PathTemplateManager  $pathTemplateManager,
        PathTemplateHandler  $pathTemplateHandler)
    {
        $this->formFactory         = $formFactory;
        $this->pathTemplateManager = $pathTemplateManager;
        $this->pathTemplateHandler = $pathTemplateHandler;
    }
    
    /**
     * Get all templates
     * @return JsonResponse
     * 
     * @Route(
     *     "",
     *     name    = "innova_path_template_list",
     *     options = {"expose"=true}
     * )
     * @Method("GET")
     */
    public function indexAction()
    {
        $templates = $this->pathTemplateManager->findAll();

        return new JsonResponse($templates);
    }
    
    /**
     * Create a new template
     * @return Response
     * 
     * @Route(
     *     "/add",
     *     name    = "innova_path_template_add",
     *     options = {"expose"=true}
     * )
     * @Method("POST")
     */
    public function addAction()
    {
        $pathTemplate = new PathTemplate();
        
        // Create form to validate data
        $form = $this->formFactory->create('innova_path_template', $pathTemplate, array (
            'csrf_protection' => false,
        ));
        
        $this->pathTemplateHandler->setForm($form);
        if ($this->pathTemplateHandler->process()) {
            // Success => modified data
            $pathTemplate = $this->pathTemplateHandler->getData();
            
            return new Response(
                $pathTemplate->getId()
            );
        }
        
        return new Response('error');
    }

    /**
     * Edit existing template
     * @return Response
     * 
     * @Route(
     *     "/edit/{id}",
     *     name    = "innova_path_template_edit",
     *     options = {"expose"=true}
     * )
     * @Method("PUT")
     */
    public function editAction(PathTemplate $pathTemplate) 
    {
        // Create form to validate data
        $form = $this->formFactory->create('innova_path_template', $pathTemplate, array (
            'method' => 'PUT',
            'csrf_protection' => false,
        ));
        
        $this->pathTemplateHandler->setForm($form);
        if ($this->pathTemplateHandler->process()) {
            // Success => modified data
            $pathTemplate = $this->pathTemplateHandler->getData();
            
            return new Response(
                $pathTemplate->getId()
            );
        }
        
        return new Response('error');
    }
    
    /**
     * Delete template
     * @return Response
     *
     * @Route(
     *     "/delete/{id}",
     *     name    = "innova_path_template_delete",
     *     options = {"expose"=true}
     * )
     * @Method("DELETE")
     */
    public function deleteAction(PathTemplate $pathTemplate) 
    {
        try {
            // Try to remove template
            $this->pathTemplateManager->delete($pathTemplate);
        
            $processed = 'success';
        } catch (\Exception $e) {
            // Error
            $processed = 'error';
        }
        
        return new Response($processed);
    }
}
