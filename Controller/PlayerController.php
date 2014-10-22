<?php

namespace Innova\PathBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;

use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * Player controller
 * @author Innovalangues <contact@innovalangues.net>
 * 
 * @Route(
 *      "",
 *      name="innova_path_player",
 *      service="innova_path.controller.path_player"
 * )
 */
class PlayerController extends ContainerAware 
{
    /**
     * Translator engine
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * Session
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * Class constructor
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Symfony\Component\Translation\TranslatorInterface         $translator
     */
    public function __construct(
        SessionInterface     $session,
        TranslatorInterface  $translator)
    {
        $this->session     = $session;
        $this->translator  = $translator;
    }

    /**
     * Display path player
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Innova\PathBundle\Entity\Path\Path                      $path
     * @param \Innova\PathBundle\Entity\Step                           $currentStep
     * @return array
     *
     * @Route(
     *      "workspace/{workspaceId}/path/{pathId}/step/{stepId}",
     *      name="innova_path_player_index",
     *      options={"expose" = true}
     * )
     * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options={"mapping": {"workspaceId": "id"}})
     * @ParamConverter("path", class="InnovaPathBundle:Path\Path", options={"mapping": {"pathId": "id"}})
     * @ParamConverter("currentStep", class="InnovaPathBundle:Step", options={"mapping": {"stepId": "id"}})
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function displayAction(Workspace $workspace, Path $path, Step $currentStep)
    {
        // we need to know if we are already playing the pass or comming from path list in order to show tree-brower or not
        $request = $this->container->get('request');
        $referer = $request->headers->get('referer'); // prev route
        $current = $request->get('_route'); // current route
        $router = $this->container->get('router');
        $matcher = $router->getMatcher();   
        
        // get previous url        
        $lastPath = substr($referer, strpos($referer, $request->getBaseUrl()));
        $lastPath = str_replace($request->getBaseUrl(), '', $lastPath);
    
        $parameters = $matcher->match($lastPath);
        $previous = $parameters['_route'];
        
        // if previous url does not match displayAction route we have to display the tree-browser modal
        $showTree = $previous != $current ? true:false;

        return array (
            'workspace' => $workspace,
            'path' => $path,
            'currentStep' => $currentStep,
            'edit' => false,
            'showTree' => $showTree,
        );
    }
    
    /**
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:components/breadcrumbs.html.twig")
     */
    public function displayBreadcrumbsAction(Workspace $workspace, Path $path, Step $currentStep)
    {
        $ghost = array ();
        $session = $this->container->get('request')->getSession();
        $lastStepId = $session->get('lastStepId');
        $lastStep = $this->container->get('doctrine')->getManager()->getRepository("InnovaPathBundle:Step")->findOneById($lastStepId);

        if($lastStep){
            $currentStepLevel = $currentStep->getLvl();
            $lastStepParents = $lastStep->getParents();

            if ($lastStepParents) {
                if (in_array($currentStep, $lastStepParents)) {
                    foreach ($lastStepParents as $lastStepParent) {
                        if ($lastStepParent->getLvl() > $currentStepLevel) {
                            $ghost[] = $lastStepParent;
                        }
                    }
                    $ghost[] = $lastStep;
                }
            }
        }

        $session->set('lastStepId', $currentStep->getId());

        return array (
            'workspace' => $workspace,
            'path' => $path,
            'currentStep' => $currentStep,
            'ghost' => $ghost,
        );
    }

    /**
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:components/resources.html.twig")
     */
    public function displayResourcesAction(Step $currentStep)
    {
        return array (
            'currentStep' => $currentStep,
        );
    }
    
    /**
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:components/tree-browser.html.twig")
     */
    public function displayTreeBrowserAction(Workspace $workspace, Path $path, Step $currentStep)
    {
        return array (
            'workspace' => $workspace,
            'path' => $path,
            'currentStep' => $currentStep,
        );
    }
    
    /**
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:components/current-step.html.twig")
     */
    public function displayCurrentStepAction(Workspace $workspace, Path $path, Step $currentStep, $edit)
    {
        $form = $this->container->get('form.factory')->create('innova_step', $currentStep, array ('method' => 'POST', 'action' => $this->container->get('router')->generate('innova_path_save_current_step', array( 'stepId' => $currentStep->getId(),'workspaceId' => $workspace->getId(),'pathId' => $path->getId()))));
        
        return array (
            'workspace' => $workspace,
            'path' => $path,
            'currentStep' => $currentStep,
            'edit' => $edit,
            'form' => $form->createView()
        );
    }

    /**
     *
     * @Route("/set-do-not-display-anymore", name="setDoNotDisplayAnymore", options={"expose"=true})
     * @Method("GET")
     */
    public function setDoNotDisplayAnymoreAction(Request $request)
    {
        $isChecked = $request->query->get('isChecked');
        $pathId = $request->query->get('pathId');

        $session = $this->container->get('request')->getSession();
        if(!$doNotDisplay = $session->get('doNotDisplayAnymore')){
            $doNotDisplay = array();
        }

        $doNotDisplay[$pathId] = $isChecked;

        $session->set('doNotDisplayAnymore', $doNotDisplay);

        return new JsonResponse(
            array('isChecked' => $doNotDisplay[$pathId])
        );
    }

    /**
     *
     * @Route("/get-do-not-display-anymore", name="getDoNotDisplayAnymore", options={"expose"=true})
     * @Method("GET")
     */
    public function getDoNotDisplayAnymoreAction(Request $request)
    {
        $pathId = $request->query->get('pathId');
        $isChecked = null;

        $session = $this->container->get('request')->getSession();
        if($doNotDisplay = $session->get('doNotDisplayAnymore') && isset($doNotDisplay[$pathId]) && $doNotDisplay[$pathId]) {
            $isChecked = true;
        } else {
            $isChecked = false;
        }

        return new JsonResponse(
            array('isChecked' => $isChecked)
        );
    }
}