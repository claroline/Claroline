<?php

namespace Innova\PathBundle\Controller\Wizard;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;

use Innova\PathBundle\Manager\StepManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

/**
 * Player controller
 * @author Innovalangues <contact@innovalangues.net>
 * 
 * @Route(
 *      "workspace/{workspaceId}/path/{pathId}",
 *      name = "innova_path_player",
 *      service = "innova_path.controller.path_player"
 * )
 * @ParamConverter("workspace", class = "ClarolineCoreBundle:Workspace\Workspace", options = { "mapping": {"workspaceId": "id"} })
 * @ParamConverter("path",      class = "InnovaPathBundle:Path\Path",              options = { "mapping": {"pathId": "id"} })
 */
class PlayerController
{
    /**
     * Step Manager
     * @var \Innova\PathBundle\Manager\StepManager
     */
    protected $stepManager;

    /**
     * Router
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * Session
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * Current request
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * Class constructor
     * @param \Symfony\Component\Routing\RouterInterface                 $router
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Innova\PathBundle\Manager\StepManager                     $stepManager
     */
    public function __construct(RouterInterface $router, SessionInterface $session, StepManager $stepManager)
    {
        $this->router      = $router;
        $this->session     = $session;
        $this->stepManager = $stepManager;
    }

    /**
     * Set current request
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @return \Innova\PathBundle\Controller\PlayerController
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Display path player
     * @param  \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param  \Innova\PathBundle\Entity\Path\Path              $path
     * @param  \Innova\PathBundle\Entity\Step                   $currentStep
     * @return array
     *
     * @Route(
     *      "/step/{stepId}",
     *      name = "innova_path_player_index",
     *      options = { "expose" = true }
     * )
     * @ParamConverter("currentStep", class="InnovaPathBundle:Step", options = { "mapping": {"stepId": "id"} })
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function displayAction(Workspace $workspace, Path $path, Step $currentStep)
    {
        // Check if path summary needs to be displayed automatically
        $autoDisplaySummary = $this->isSummaryAutoDisplayed($path, $currentStep);

        // Build road back to the previous visited step
        $roadBack = $this->buildRoadBack($currentStep);

        // Store current step in session to can build road back when User will leave it
        $this->session->set('lastStepId', $currentStep->getId());

        return array (
            'workspace'          => $workspace,
            'path'               => $path,
            'currentStep'        => $currentStep,
            'roadBack'           => $roadBack,
            'autoDisplaySummary' => $autoDisplaySummary,
            'autoDisplayEnabled' => $this->isAutoDisplayEnabled($path)
        );
    }

    /**
     * Build road back to the last visited step
     * @param  \Innova\PathBundle\Entity\Step $currentStep
     * @return Array
     */
    private function buildRoadBack(Step $currentStep)
    {
        $roadBack = array ();

        $lastStepId = $this->session->get('lastStepId');
        $lastStep   = $this->stepManager->get($lastStepId);

        if ($lastStep) {
            $currentStepLevel = $currentStep->getLvl();
            $lastStepParents = $lastStep->getParents();

            if ($lastStepParents) {
                if (in_array($currentStep, $lastStepParents)) {
                    foreach ($lastStepParents as $lastStepParent) {
                        if ($lastStepParent->getLvl() > $currentStepLevel) {
                            $roadBack[] = $lastStepParent;
                        }
                    }
                    $roadBack[] = $lastStep;
                }
            }
        }

        return $roadBack;
    }

    /**
     * Check if summary needs to be displayed (if the User just arrived on the Path)
     */
    private function isSummaryAutoDisplayed(Path $path, Step $currentStep)
    {
        $showSummary = false;

        if (null == $currentStep->getParent()) {
            // We are on the root step of the path
            // Check if User has disabled auto display of the summary
            if ($this->isAutoDisplayEnabled($path)) {
                // Auto display is not disabled for this path
                // Check if User comes from another part of the app
                $referrer = $this->request->headers->get('referer');
                if (!empty($referrer)) {
                    $context = $this->router->getContext();
                    $currentMethod = $context->getMethod();
                    $context->setMethod('HEAD');

                    $baseUrl = $this->request->getBaseUrl();

                    if (false !== strpos($referrer, $baseUrl)) {
                        $lastPath = substr($referrer, strpos($referrer, $baseUrl) + strlen($baseUrl));
                    } else {
                        $lastPath = $referrer;
                    }

                    $previous = null;
                    try {
                        $previous = $this->router->getMatcher()->match($lastPath);
                    } catch (\Exception $e) {
                        // Do nothing on error, we just don't want a NotFound exception if URL doesn't match
                    }

                    $context->setMethod($currentMethod);

                    if (!empty($previous) && $previous['_route'] != $this->request->get('_route')) {
                        $showSummary = true;
                    }
                }
            }
        }

        return $showSummary;
    }

    private function isAutoDisplayEnabled(Path $path)
    {
        $enabled = true;

        $disabledSummaryAutoDisplay = $this->session->get('doNotDisplayAnymore');
        if (!empty($disabledSummaryAutoDisplay) && !empty($disabledSummaryAutoDisplay[$path->getId()])) {
            $enabled = false;
        }

        return $enabled;
    }

    /**
     *
     * @Route(
     *      "/disable_summary/{disabled}",
     *      name     = "innova_path_player_toggle_summary",
     *      defaults = { "disabled" = false },
     *      options  = { "expose"   = true }
     * )
     * @Method("PUT")
     */
    public function toggleSummaryAutoDisplay(Workspace $workspace, Path $path, $disabled)
    {
        $disabled = filter_var($disabled, FILTER_VALIDATE_BOOLEAN);

        $disabledSummaryAutoDisplay = $this->session->get('doNotDisplayAnymore');
        if (empty($disabledSummaryAutoDisplay)) {
            $disabledSummaryAutoDisplay = array ();
        }

        if ($disabled) {
            $disabledSummaryAutoDisplay[$path->getId()] = $disabled;
        } else if (!empty($disabledSummaryAutoDisplay[$path->getId()])) {
            unset($disabledSummaryAutoDisplay[$path->getId()]);
        }

        $this->session->set('doNotDisplayAnymore', $disabledSummaryAutoDisplay);

        return new JsonResponse($disabled);
    }
}
