<?php

namespace Innova\PathBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Player controller
 * @author Innovalangues <contact@innovalangues.net>
 * 
 * @Route(
 *      "",
 *      name="innova_path_player",
 *      service="innova.controller.path_player"
 * )
 */
class PlayerController extends ContainerAware
{
    /**
     * Display path player
     * @param  Path $path
     * @return array
     * 
     * @Route(
     *      "workspace/{workspaceId}/path/{pathId}",
     *      name="innova_path_player_index",
     *      options={"expose" = true}
     * )
     * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"mapping": {"workspaceId": "id"}})
     * @ParamConverter("path", class="InnovaPathBundle:Path", options={"mapping": {"pathId": "id"}})
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function displayAction(AbstractWorkspace $workspace, Path $path, Step $currentStep = null)
    {
        // Get root step of path if no step requested
        if (empty($currentStep)) {
            $steps = $path->getSteps();
            if (!empty($steps) && !empty($steps[0])) {
                // Root step exists => grab it
                $currentStep = $steps[0];
            }
        }
        
        return array (
            'workspace' => $workspace,
            'path' => $path,
            'currentStep' => $currentStep
        );
    }
    
    /**
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:components/breadcrumbs.html.twig")
     */
    public function displayBreadcrumbsAction(AbstractWorkspace $workspace, Path $path, Step $currentStep = null)
    {
        return array ();
    }
    
    /**
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:components/resources.html.twig")
     */
    public function displayResourcesAction()
    {
        return array ();
    }
    
    /**
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:components/squares-browser.html.twig")
     */
    public function displaySquaresBrowserAction()
    {
        return array ();
    }
    
    /**
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:components/current-step.html.twig")
     */
    public function displayCurrentStepAction(Step $currentStep)
    {
        return array (
            'currentStep' => $currentStep,
        );
    }
}