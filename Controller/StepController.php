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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Innova\PathBundle\Manager\StepManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

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
*/
class StepController extends Controller
{
    private $manager;

    /**
     * @InjectParams({
     *     "manager"        = @Inject("innova.manager.step_manager"),
     * })
     */
    public function __construct(StepManager $manager)
    {
        $this->manager = $manager;
    }

     /**
     * Finds and displays a Step entity.
     *
     * @Route("workspace/{workspaceId}/path/{pathId}/step/{stepId}", name="innova_step_show")
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function showAction($workspaceId, $pathId, $stepId)
    {
        $em = $this->entityManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $step = $em->getRepository('InnovaPathBundle:Step')->findOneById($stepId);
        $path = $em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathId);

        $children = $em->getRepository('InnovaPathBundle:Step')->findByParent($step);

        $resources = $this->manager->getStepResourceNodes($step);

        return array(
            'step' => $step,
            'resources' => $resources,
            'path' => $path,
            'workspace' => $workspace,
            'children' => $children
        );
    }


    /**
     * Finds and displays a Step entity.
     *
     * @Route("/path/{pathId}/step/{stepId}", name="innova_pass_show")
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:pass.html.twig")
     */
    public function showPassAction($pathId, $stepId)
    {
        $em = $this->entityManager();
        $step = $em->getRepository('InnovaPathBundle:Step')->findOneById($stepId);
        $path = $em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathId);
        $root = $em->getRepository('InnovaPathBundle:Step')->findOneBy(array('path' => $path, 'parent' => null));
        $children = $em->getRepository('InnovaPathBundle:Step')->findByParent($step);
        $siblings = $em->getRepository('InnovaPathBundle:Step')->findBy(array('parent' => $step->getParent(), 'path' => $path));
        $resources = $this->manager->getStepResourceNodes($step);

        $fullPath = array();
        $this->getFullPath($step, $path, $fullPath);
        
        $allParents = array();
        $this->getAllParents($step, $allParents);

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


    private function getAllParents($step, &$allParents){
        if ($step->getParent()){
            $allParents[$step->getParent()->getLvl()] = $step->getParent();
            $this->getAllParents($step->getParent(), $allParents);
        }
    }


    private function getFullPath($step, $path, &$fullPath){
        $em = $this->entityManager();
        if($stepParent = $step->getParent()){
            if($parentSiblings = $em->getRepository('InnovaPathBundle:Step')->findBy(array('parent' => $stepParent->getParent(), 'path' => $path))){
                foreach($parentSiblings as $parentSibling){
                    $fullPath[$stepParent->getLvl()][] = $parentSibling;
                }
                $this->getFullPath($parentSiblings[0], $path, $fullPath);
            }
        }
        
    }

    /*
    private function pathConstruct($step, $level, &$fullPath){
        $em = $this->entityManager();
        $fullPath[$level][] = $step;
        if($children = $em->getRepository('InnovaPathBundle:Step')->findByParent($step)){
            foreach ($children as $child){
                $this->pathConstruct($child, $level+1, $fullPath);
            }
        }
    }
    */


    /**
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
        $em = $this->entityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $resourceNodes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findByCreator($user);
        
        $resourceTypeToShow = array("1", "3", "5", "7", "10");
        $resources = array();

        foreach ($resourceNodes as $resourceNode) {
            if (in_array( $resourceNode->getResourceType()->getId(), $resourceTypeToShow)) {
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
     * 
     * @Route("/step/herited_resources/{stepId}", name="innova_herited_resources", options = {"expose"=true})
     * @Method("GET")
     */
    public function getHeritedResources($stepId)
    {
        $em = $this->entityManager();
        $step = $em->getRepository('InnovaPathBundle:Step')->findOneById($stepId);

        $heritedResources = array();

        // création d'un tableau de ressources exclus à partir de la collection doctrine.
        $excludedStep2ResourceNodes = $em->getRepository('InnovaPathBundle:Step2ResourceNode')->findBy(array('step' => $step, 'excluded' => true));
        $excludedResources = array();
        foreach($excludedStep2ResourceNodes as $excludedStep2ResourceNode){
            $excludedResources[] = $excludedStep2ResourceNode->getResourceNode()->getId();
        }

        // si le step a des parents on check les ressources partagées du parent
        if($parent = $em->getRepository('InnovaPathBundle:Step')->findOneById($stepId)->getParent()){
            $this->getPropagatedResources($parent, $heritedResources, $excludedResources);
        }

        // on reverse le tableau pour avoir les ressources de plus haut niveau en haut dans la vue.
        $heritedResources = array_reverse($heritedResources);

        return $this->render('InnovaPathBundle:Player:partial/herited-resources.html.twig', array(
            'heritedResources' => $heritedResources
        ));
    }


    private function getPropagatedResources($step, &$heritedResources, $excludedResources){
        $resources = $this->manager->getStepPropagatedResourceNodes($step);
        foreach ($resources["digital"] as $resource) {
            if(!in_array($resource->getId(), $excludedResources)){
                $heritedResources[$step->getName()]["digital"][] =  $resource;
            }
        }
        foreach ($resources["nonDigital"] as $resource) {
            if(!in_array($resource->getResourceNode()->getId(), $excludedResources)){
                $heritedResources[$step->getName()]["nonDigital"][] =  $resource;
            }
        }
        if ($step->getParent()){
            $this->getPropagatedResources($step->getParent(), $heritedResources, $excludedResources);
        }
        
        return $heritedResources;
    }


    /**
     * Load available Step images
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
        $request = $this->get('request');
        $imagesPath = $request->server->get('DOCUMENT_ROOT') . $request->getBasePath() . '/bundles/innovapath/images/steps/';
        
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
     * entityManager function
     *
     * @return $em
     *
     */
    public function entityManager()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        return $em;
    }

}
