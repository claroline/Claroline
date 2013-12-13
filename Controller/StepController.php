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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

// Controller dependencies
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Innova\PathBundle\Manager\StepManager;

use Innova\PathBundle\Entity\Step;

/**
 * Class StepController
 *
 * @category   Controller
 * @package    Innova
 * @subpackage PathBundle
 * @author     Innovalangues <contant@innovalangues.net>
 * @copyright  2013 Innovalangues
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    0.1
 * @link       http://innovalangues.net
 * 
 * @Route(
 *      "",
 *      name = "innova_step",
 *      service="innova.controller.step"
 * )
 */
class StepController
{
    /**
     * Current entity manager for data persist
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    
    /**
     * Current security context
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;
    
    /**
     * Current request
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    
    /**
     * Current path manager
     * @var \Innova\PathBundle\Manager\PathManager;
     */
    protected $pathManager;
    
    /**
     * Class constructor
     * Inject needed dependencies
     * @param EntityManagerInterface   $entityManager
     * @param SecurityContextInterface $securityContext
     * @param StepManager              $stepManager
     */
    public function __construct(
	    EntityManagerInterface   $entityManager,
        SecurityContextInterface $securityContext,
        StepManager              $stepManager
    )
    {
        $this->entityManager   = $entityManager;
        $this->securityContext = $securityContext;
        $this->stepManager     = $stepManager;
    }

    /**
     * Inject current request into service
     * @param Request $request
     * @return \Innova\PathBundle\Controller\StepController
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    
        return $this;
    }

    /**
     * Get available resources for current user
     * @return JsonResponse
     * 
     * @Route(
     *      "/step/resources", 
     *      name="innova_user_resources", 
     *      options = {"expose"=true}
     * )
     * @Method("GET")
     */
    public function getUserResourcesAction()
    {
        // TODO : put it into a manager or repository
        $user = $this->securityContext->getToken()->getUser();
        $resourceNodes = $this->entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findByCreator($user);
        
        $resources = array();

        foreach ($resourceNodes as $resourceNode) {
                $resource = new \stdClass();
                $resource->id = $resourceNode->getId();
                $resource->workspace = $resourceNode->getWorkspace()->getName();
                $resource->name = $resourceNode->getName();
                $resource->type = $resourceNode->getResourceType()->getName();
                $resource->icon = $resourceNode->getIcon()->getIconLocation();

                $resources[] = $resource;
        }
        return new JsonResponse($resources);
    }

    /**
     * Load available Step images
     * @return JsonResponse
     * 
     * @Route(
     *      "/sep/images",
     *      name="innova_step_images",
     *      options = {"expose"=true}
     * )
     * @Method("GET")
     */
    public function getImagesAction() 
    {
        // TODO : put it into a manager or repository
        $images = array ();
        
        $authorizedExtensions = array ('png', 'jpg', 'jpeg', 'tiff', 'gif');
        $imagesPath = $this->request->server->get('DOCUMENT_ROOT') . $this->request->getBasePath() . '/bundles/innovapath/images/steps/';
        
        // Get all content of directory
        $imagesDir = dir($imagesPath);
        if ($imagesDir) {
            while ($entry = $imagesDir->read()) {
                $filename = $imagesPath . $entry;
                if (is_file($filename)) {
                    // Current element is a file => check extension to see if it's an authorized image
                    $fileInfo = pathinfo($entry);
                    if (!empty($fileInfo) && !empty($fileInfo['extension']) && in_array($fileInfo['extension'], $authorizedExtensions)) {
                        // Authorized file => get it
                        $images[] = $entry;
                    }
                }
            }
            $imagesDir->close();
        }
        
        return new JsonResponse($images);
    }

    /**
     * Load available values for step where property
     * @return JsonResponse
     * 
     * @Route(
     *     "/step/where",
     *     name = "innova_path_get_stepwhere",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     */
    public function getStepWheresAction()
    {
        // TODO : put it into a manager or repository
        $results = $this->entityManager->getRepository('InnovaPathBundle:StepWhere')->findAll();
    
        $stepWheres = array();
        foreach ($results as $result) {
            $stepWheres[$result->getId()] = $result->getName();
        }
    
        return new JsonResponse($stepWheres);
    }
    
    /**
     * Load available values for step who property
     * @return JsonResponse
     * 
     * @Route(
     *     "/step/who",
     *     name = "innova_path_get_stepwho",
     *     options = {"expose"=true}
     * )
     * @Method("GET")
     */
    public function getStepWhosAction()
    {
        // TODO : put it into a manager or repository
        $results = $this->entityManager->getRepository('InnovaPathBundle:StepWho')->findAll();
    
        $stepWhos = array();
        foreach ($results as $result) {
            $stepWhos[$result->getId()] = $result->getName();
        }
    
        return new JsonResponse($stepWhos);
    }
}
