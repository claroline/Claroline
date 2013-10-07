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

use \Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\StepManager;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\ResourceActivity;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\User;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Manager\Exception\ResourceMoveException;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Event\StrictDispatcher;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;


/**
 * Class ResourceController
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
class ResourceController extends Controller
{
    private $stepManager;
    private $sc;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $translator;
    private $request;
    private $dispatcher;
    private $maskManager;

    /**
     * @DI\InjectParams({
     *     "sc"              = @DI\Inject("security.context"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "stepManager"     = @Inject("innova.manager.step_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"      = @DI\Inject("translator"),
     *     "request"         = @DI\Inject("request"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct
    (
        SecurityContext $sc,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        Translator $translator,
        Request $request,
        StrictDispatcher $dispatcher,
        MaskManager $maskManager,
        StepManager $stepManager
    )
    {
        $this->sc = $sc;
        $this->resourceManager = $resourceManager;
        $this->translator = $translator;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->request = $request;
        $this->dispatcher = $dispatcher;
        $this->maskManager = $maskManager;
        $this->stepManager = $stepManager;
    }

    /**
     *
     * @Route(
     *     "workspace/{workspace}/path/{path}/step/{step}/text/{node}",
     *     name = "innova_text_open",
     *     options = {"expose"=true}
     * )
     * @Template("InnovaPathBundle::Player/text.html.twig")
     *
     */
    public function openTextAction(AbstractWorkspace $workspace, $path, $step, ResourceNode $node)
    {  
        $this->preOpenAction($node);
        $em = $this->entityManager();

        $step = $em->getRepository('InnovaPathBundle:Step')->findOneByResourceNode($step);
        $path = $em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($path);
        $resources = $this->stepManager->getStepResourceNodes($step);
        $children = $em->getRepository('InnovaPathBundle:Step')->findByParent($step);

        $text = $this->resourceManager->getResourceFromNode($node);
        $collection = new ResourceCollection(array($text->getResourceNode()));
        $isGranted = $this->container->get('security.context')->isGranted('WRITE', $collection);
        $revisionRepo = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\Revision');

        $this->postOpenAction($node);

        return array(
            'workspace' => $workspace,
            'resources' => $resources,
            'children' => $children,
            'step' => $step,
            'path' => $path,
            'text' => $revisionRepo->getLastRevision($text)->getContent(),
            '_resource' => $text,
            'isEditGranted' => $isGranted
        );
    }

     /**
     *
     * @Route(
     *     "workspace/{workspace}/path/{path}/step/{step}/activity/{node}",
     *     name = "innova_activity_open",
     *     options = {"expose"=true}
     * )
     * @Template("InnovaPathBundle::Player/activity.html.twig")
     *
     */
    public function openActivityAction(AbstractWorkspace $workspace, $path, $step, $node)
    {
        $em = $this->entityManager();

        $activity = $em->getRepository('ClarolineCoreBundle:Resource\Activity')->findOneByResourceNode($node);
        $step = $em->getRepository('InnovaPathBundle:Step')->findOneByResourceNode($step);
        $path = $em->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($path);
        $resources = $this->stepManager->getStepResourceNodes($step);
        $children = $em->getRepository('InnovaPathBundle:Step')->findByParent($step);

        $resourceActivities = $em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($activity);
        $resource = isset($resourceActivities[0]) ? $resourceActivities[0]->getResourceNode(): null;

        return array(
            'workspace' => $workspace,
            'resources' => $resources,
            'children' => $children,
            'step' => $step,
            'path' => $path,
            'resource' => $resource,
            'activity' => $activity
        );
    }





    private function getRealTarget(ResourceNode $node)
    {
        if ($node->getClass() === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $resource = $this->resourceManager->getResourceFromNode($node);
            if ($resource === null) {
                throw new \Exception('The resource was removed.');
            }
            $node = $resource->getTarget();
            if ($node === null) {
                throw new \Exception('The node target was removed.');
            }
        }

        return $node;
    }

    /**
     * Checks if the current user has the right to perform an action on a ResourceCollection.
     * Be careful, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     * @param string             $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    public function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->sc->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
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

    private function preOpenAction(ResourceNode $node)
    {  
        $collection = new ResourceCollection(array($node));

        $node = $this->getRealTarget($node);
        $this->checkAccess('OPEN', $collection);

    }

    private function postOpenAction(ResourceNode $node)
    {  
         $this->dispatcher->dispatch('log', 'Log\LogResourceRead', array($node));
    }
}
