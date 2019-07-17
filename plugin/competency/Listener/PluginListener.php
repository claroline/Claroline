<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Manager\ToolManager;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use HeVinci\CompetencyBundle\Manager\ObjectiveManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
    /** @var ObjectiveManager */
    private $objectiveManager;
    /** @var TwigEngine */
    private $templating;
    /** @var TokenStorageInterface */
    private $tokenStorage;
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
     *     "objectiveManager"  = @DI\Inject("hevinci.competency.objective_manager"),
     *     "templating"        = @DI\Inject("templating"),
     *     "tokenStorage"      = @DI\Inject("security.token_storage"),
     *     "toolManager"       = @DI\Inject("claroline.manager.tool_manager"),
     *     "stack"             = @DI\Inject("request_stack"),
     *     "kernel"            = @DI\Inject("http_kernel")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param CompetencyManager             $competencyManager
     * @param ObjectiveManager              $objectiveManager
     * @param TwigEngine                    $templating
     * @param TokenStorageInterface         $tokenStorage
     * @param ToolManager                   $toolManager
     * @param RequestStack                  $stack
     * @param HttpKernelInterface           $kernel
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CompetencyManager $competencyManager,
        ObjectiveManager $objectiveManager,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        ToolManager $toolManager,
        RequestStack $stack,
        HttpKernelInterface $kernel
    ) {
        $this->authorization = $authorization;
        $this->competencyManager = $competencyManager;
        $this->objectiveManager = $objectiveManager;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
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
        $user = $this->tokenStorage->getToken()->getUser();
        $objectives = 'anon.' !== $user ? $this->objectiveManager->loadSubjectObjectives($user) : [];
        $objectivesCompetencies = [];
        $competencies = [];

        foreach ($objectives as $objectiveData) {
            $objective = $this->objectiveManager->getObjectiveById($objectiveData['id']);
            $objectiveComps = $this->objectiveManager->loadUserObjectiveCompetencies($objective, $user);
            $objectivesCompetencies[$objectiveData['id']] = $objectiveComps;
            $competencies[$objectiveData['id']] = [];

            foreach ($objectiveComps as $comp) {
                if (isset($comp['__children']) && count($comp['__children']) > 0) {
                    $this->objectiveManager->getCompetencyFinalChildren(
                        $comp,
                        $competencies[$objectiveData['id']],
                        $comp['levelValue'],
                        $comp['nbLevels']
                    );
                } else {
                    $comp['id'] = $comp['originalId'];
                    $comp['requiredLevel'] = $comp['levelValue'];
                    $competencies[$objectiveData['id']][$comp['id']] = $comp;
                }
            }
        }

        $event->setData([
            'objectives' => $objectives,
            'objectivesCompetencies' => $objectivesCompetencies,
            'competencies' => $competencies,
        ]);
        $event->stopPropagation();
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
