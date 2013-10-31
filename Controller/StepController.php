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
use Symfony\Component\Security\Core\SecurityContextInterface;
use Innova\PathBundle\Manager\StepManager;

use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

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
 *      service="innova.step.controller"
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
     * Finds and displays a Step entity.
     * @return array
     * 
     * @Route(
     *      "workspace/{workspaceId}/path/{pathId}/step/{stepId}", 
     *      name="innova_step_show"
     * )
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function showAction($workspaceId, $pathId, $stepId)
    {
        // TODO : put it into a manager or repository
        $workspace = $this->entityManager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $step = $this->entityManager->getRepository('InnovaPathBundle:Step')->findOneById($stepId);
        $path = $this->entityManager->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathId);
        $root = $this->entityManager->getRepository('InnovaPathBundle:Step')->findOneBy(array('path' => $path, 'parent' => null));
        $children = $this->entityManager->getRepository('InnovaPathBundle:Step')->findByParent($step);
        $siblings = $this->entityManager->getRepository('InnovaPathBundle:Step')->findBy(array('parent' => $step->getParent(), 'path' => $path));
        
        $resources = $this->stepManager->getStepResourceNodes($step);

        $fullPath = array();
        $this->getFullPath($step, $path, $fullPath);
        
        $allParents = array();
        $this->getAllParents($step, $allParents);

       return array(
            'workspace' => $workspace,
            'step' => $step,
            'siblings' => $siblings,
            'resources' => $resources,
            'fullPath' => $fullPath,
            'path' => $path,
            'children' => $children,
            'allParents' => $allParents,
            'root' => $root
        );
    }

    /**
     * Finds and displays a Step entity.
     * @return array
     * 
     * @Route("/path/{pathId}/step/{stepId}", name="innova_pass_show")
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:pass.html.twig")
     */
    public function showPassAction($pathId, $stepId)
    {
        // TODO : put it into a manager or repository
        $step = $this->entityManager->getRepository('InnovaPathBundle:Step')->findOneById($stepId);
        $path = $this->entityManager->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathId);
        $root = $this->entityManager->getRepository('InnovaPathBundle:Step')->findOneBy(array('path' => $path, 'parent' => null));
        $children = $this->entityManager->getRepository('InnovaPathBundle:Step')->findByParent($step);
        $siblings = $this->entityManager->getRepository('InnovaPathBundle:Step')->findBy(array('parent' => $step->getParent(), 'path' => $path));
        $resources = $this->stepManager->getStepResourceNodes($step);
        
        return array(
            'step' => $step,
            'siblings' => $siblings,
            'resources' => $resources,
            'fullPath' => $fullPath,
            'path' => $path,
            'children' => $children,
            'allParents' => $allParents,
            'root' => $root
        );
    }

    /**
     * @todo put it into a manager or repository and remove function
     */
    private function getAllParents($step, &$allParents)
    {
        if ($step->getParent()) {
            $allParents[$step->getParent()->getLvl()] = $step->getParent();
            $this->getAllParents($step->getParent(), $allParents);
        }
    }

    /**
     * @todo put it into a manager or repository and remove function
     */
    private function getFullPath($step, $path, &$fullPath)
    {
        if ($stepParent = $step->getParent()) {
            if ($parentSiblings = $this->entityManager->getRepository('InnovaPathBundle:Step')->findBy(array('parent' => $stepParent->getParent(), 'path' => $path))) {
                foreach ($parentSiblings as $parentSibling) {
                    $fullPath[$stepParent->getLvl()][] = $parentSibling;
                }
                $this->getFullPath($parentSiblings[0], $path, $fullPath);
            }
        }
        
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
        
        $resourceTypeToShow = array("1", "3", "5", "7", "10");
        $resources = array();

        foreach ($resourceNodes as $resourceNode) {
            if (in_array($resourceNode->getResourceType()->getId(), $resourceTypeToShow)) {
                $resource = new \stdClass();
                $resource->id = $resourceNode->getId();
                $resource->workspace = $resourceNode->getWorkspace()->getName();
                $resource->name = $resourceNode->getName();
                $resource->type = $resourceNode->getResourceType()->getName();
                $resource->icon = $resourceNode->getIcon()->getIconLocation();

                $resources[] = $resource;
            }
        }
        return new JsonResponse($resources);
    }

    /**
     * Get herited resources from parent steps
     * @return array
     * 
     * @Route(
     *      "/step/herited_resources/{stepId}", 
     *      name="innova_herited_resources", 
     *      options = {"expose"=true}
     * )
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:partial/herited-resources.html.twig")
     */
    public function getHeritedResources($stepId)
    {
        // TODO : put it into a manager or repository
        $step = $this->entityManager->getRepository('InnovaPathBundle:Step')->findOneById($stepId);

        $heritedResources = array();

        // création d'un tableau de ressources exclus à partir de la collection doctrine.
        $excludedStep2ResourceNodes = $this->entityManager->getRepository('InnovaPathBundle:Step2ResourceNode')->findBy(array('step' => $step, 'excluded' => true));
        $excludedResources = array();
        foreach($excludedStep2ResourceNodes as $excludedStep2ResourceNode){
            $excludedResources[] = $excludedStep2ResourceNode->getResourceNode()->getId();
        }

        // si le step a des parents on check les ressources partagées du parent
        if($parent = $this->entityManager->getRepository('InnovaPathBundle:Step')->findOneById($stepId)->getParent()){
            $this->getPropagatedResources($parent, $heritedResources, $excludedResources);
        }

        // on reverse le tableau pour avoir les ressources de plus haut niveau en haut dans la vue.
        $heritedResources = array_reverse($heritedResources);

        /*return $this->render('InnovaPathBundle:Player:partial/herited-resources.html.twig', array(
            'heritedResources' => $heritedResources
        ));*/
        
        return array (
            'heritedResources' => $heritedResources,
        );
    }

    /**
     * @todo put it into a manager or repository and remove function
     */
    private function getPropagatedResources($step, &$heritedResources, $excludedResources)
    {
        $resources = $this->stepManager->getStepPropagatedResourceNodes($step);
        
        if (!empty($resources)) {
            if (!empty($resources['digital'])) {
                foreach ($resources['digital'] as $resource) {
                    if (!in_array($resource->getId(), $excludedResources)) {
                        $heritedResources[$step->getName()]['digital'][] =  $resource;
                    }
                }
            }
            
            if (!empty($resources['nonDigital'])) {
                foreach ($resources['nonDigital'] as $resource) {
                    if(!in_array($resource->getResourceNode()->getId(), $excludedResources)){
                        $heritedResources[$step->getName()]['nonDigital'][] =  $resource;
                    }
                }
            }
        }
        
        if ($step->getParent()) {
            $this->getPropagatedResources($step->getParent(), $heritedResources, $excludedResources);
        }
        
        return $heritedResources;
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
