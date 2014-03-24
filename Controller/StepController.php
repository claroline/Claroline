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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

// Controller dependencies
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Innova\PathBundle\Manager\StepManager;

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
 *      service="innova_path.controller.step"
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
     * Current path manager
     * @var \Innova\PathBundle\Manager\StepManager
     */
    protected $stepManager;
    
    /**
     * Path to the kernel
     * @var string
     */
    protected $kernelRoot;
    
    /**
     * Class constructor
     * Inject needed dependencies
     * @param EntityManagerInterface   $entityManager
     * @param SecurityContextInterface $securityContext
     * @param StepManager              $stepManager
     */
    public function __construct(
        $kernelRoot,
	    EntityManagerInterface   $entityManager,
        SecurityContextInterface $securityContext,
        StepManager              $stepManager) 
    {
        $this->kernelRoot      = $kernelRoot;
        $this->entityManager   = $entityManager;
        $this->securityContext = $securityContext;
        $this->stepManager     = $stepManager;
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
        $user = $this->securityContext->getToken()->getUser();
        $resourceNodes = $this->entityManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findByCreator($user);
        
        $resources = array();

        foreach ($resourceNodes as $resourceNode) {
            $resource = new \stdClass();
            $resource->id = $resourceNode->getId();
            $resource->workspace = $resourceNode->getWorkspace()->getName();
            $resource->name = $resourceNode->getName();
            $resource->type = $resourceNode->getResourceType()->getName();
            $resource->icon = $resourceNode->getIcon()->getRelativeUrl();

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
        $images = array ();
        
        $authorizedExtensions = array ('png', 'jpg', 'jpeg', 'tiff', 'gif');
        $imagesPath = $this->kernelRoot . '/../web/bundles/innovapath/images/steps/';
        
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
    public function getWheresAction()
    {
        $wereList = $this->stepManager->getWhere();
    
        return new JsonResponse($wereList);
    }
    
    /**
     * Load default value for step where property
     * @return JsonResponse
     * 
     * @Route(
     *     "/step/where_default",
     *     name = "innova_path_get_stepwhere_default",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     */
    public function getWhereDefaultAction()
    {
        $default = $this->stepManager->getWhereDefault();

        return new JsonResponse($default);
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
    public function getWhosAction()
    {
        $whoList = $this->stepManager->getWho();
    
        return new JsonResponse($whoList);
    }

    /**
     * Load default value for step who property
     * @return JsonResponse
     * 
     * @Route(
     *     "/step/who_default",
     *     name = "innova_path_get_stepwho_default",
     *     options = {"expose"=true}
     * )
     * @Method("GET")
     */
    public function getWhoDefaultAction()
    {
        $default = $this->stepManager->getWhoDefault();

        return new JsonResponse($default);
    }
}
