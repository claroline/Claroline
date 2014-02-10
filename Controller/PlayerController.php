<?php

namespace Innova\PathBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Innova\PathBundle\Entity\Path\Path;
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
     * @param \Symfony\Component\Routing\RouterInterface   $router
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Innova\PathBundle\Form\Handler\StepHandler  $stepHandler
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
     * @param  Path $path
     * @return array
     * 
     * @Route(
     *      "workspace/{workspaceId}/path/{pathId}/step/{stepId}",
     *      name="innova_path_player_index",
     *      options={"expose" = true}
     * )
     * @ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\AbstractWorkspace", options={"mapping": {"workspaceId": "id"}})
     * @ParamConverter("path", class="InnovaPathBundle:Path\Path", options={"mapping": {"pathId": "id"}})
     * @ParamConverter("currentStep", class="InnovaPathBundle:Step", options={"mapping": {"stepId": "id"}})
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function displayAction(AbstractWorkspace $workspace, Path $path, Step $currentStep)
    {

        return array (
            'workspace' => $workspace,
            'path' => $path,
            'currentStep' => $currentStep,
            'edit' => false,
        );
    }
    
    /**
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:components/breadcrumbs.html.twig")
     */
    public function displayBreadcrumbsAction(AbstractWorkspace $workspace, Path $path, Step $currentStep)
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
    public function displayTreeBrowserAction(AbstractWorkspace $workspace, Path $path, Step $currentStep)
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
    public function displayCurrentStepAction(AbstractWorkspace $workspace, Path $path, Step $currentStep, $edit)
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
}