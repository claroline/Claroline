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
        $step = $em->getRepository('InnovaPathBundle:Step')->findOneByResourceNode($stepId);
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
     * 
     * @Route("/plop/", name="innova_user_resources", options = {"expose"=true})
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
            if(in_array( $resourceNode->getResourceType()->getId(), $resourceTypeToShow)){
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
     * entityManager function
     *
     * @return $em
     *
     */
    public function entityManager()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $em = $this->getDoctrine()->getManager();

        return $em;
    }

}
