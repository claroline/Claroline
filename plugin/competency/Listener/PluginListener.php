<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\ToolManager;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Defines the listening methods for all the core extension
 * points used in this plugin (tools and widgets).
 *
 * @DI\Service("hevinci.competency.plugin_listener")
 */
class PluginListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var CompetencyManager */
    private $competencyManager;
    /** @var TwigEngine */
    private $templating;
    /** @var ToolManager */
    private $toolManager;

    private $request;
    private $kernel;

    /**
     * PluginListener constructor.
     *
     * @DI\InjectParams({
     *     "authorization"     = @DI\Inject("security.authorization_checker"),
     *     "competencyManager" = @DI\Inject("hevinci.competency.competency_manager"),
     *     "templating"        = @DI\Inject("templating"),
     *     "toolManager"       = @DI\Inject("claroline.manager.tool_manager"),
     *     "stack"             = @DI\Inject("request_stack"),
     *     "kernel"            = @DI\Inject("http_kernel")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param CompetencyManager             $competencyManager
     * @param TwigEngine                    $templating
     * @param ToolManager                   $toolManager
     * @param RequestStack                  $stack
     * @param HttpKernelInterface           $kernel
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CompetencyManager $competencyManager,
        TwigEngine $templating,
        ToolManager $toolManager,
        RequestStack $stack,
        HttpKernelInterface $kernel
    ) {
        $this->authorization = $authorization;
        $this->competencyManager = $competencyManager;
        $this->templating = $templating;
        $this->toolManager = $toolManager;
        $this->request = $stack->getCurrentRequest();
        $this->kernel = $kernel;
    }

    /**
     * @DI\Observe("administration_tool_learning-objectives")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenLearningObjectivesTool(OpenAdministrationToolEvent $event)
    {
        $competenciesTool = $this->toolManager->getAdminToolByName('competencies');

        if (is_null($competenciesTool) || !$this->authorization->isGranted('OPEN', $competenciesTool)) {
            throw new AccessDeniedException();
        }
        $this->competencyManager->ensureHasScale();
        $content = $this->templating->render('HeVinciCompetencyBundle:objective:layout.html.twig');
        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_desktop_my-learning-objectives")
     *
     * @param DisplayToolEvent $event
     */
    public function onOpenMyLearningObjectivesTool(DisplayToolEvent $event)
    {
        $this->forward('HeVinciCompetencyBundle:MyObjective:objectives', $event);
    }

    /**
     * @DI\Observe("resource_action_manage_competencies")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onOpenResourceCompetencies(CustomActionResourceEvent $event)
    {
        $this->forward('HeVinciCompetencyBundle:Resource:competencies', $event, true);
    }

    private function forward($controller, Event $event, $withNode = false)
    {
        $attributes = ['_controller' => $controller];

        if ($event instanceof CustomActionResourceEvent) {
            $attributes['id'] = $withNode ?
                $event->getResource()->getResourceNode()->getId() :
                $event->getResource()->getId();
        }

        $subRequest = $this->request->duplicate([], null, $attributes);
        $response = $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        if ($event instanceof DisplayToolEvent) {
            $event->setContent($response->getContent());
        } else {
            $event->setResponse($response);
        }

        $event->stopPropagation();
    }
}
