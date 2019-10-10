<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use HeVinci\CompetencyBundle\Manager\ObjectiveManager;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Defines the listening methods for all the core extension
 * points used in this plugin (tools and widgets).
 */
class PluginListener
{
    /** @var CompetencyManager */
    private $competencyManager;
    /** @var ObjectiveManager */
    private $objectiveManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    private $request;
    private $kernel;

    /**
     * PluginListener constructor.
     *
     * @param CompetencyManager     $competencyManager
     * @param ObjectiveManager      $objectiveManager
     * @param TokenStorageInterface $tokenStorage
     * @param RequestStack          $stack
     * @param HttpKernelInterface   $kernel
     */
    public function __construct(
        CompetencyManager $competencyManager,
        ObjectiveManager $objectiveManager,
        TokenStorageInterface $tokenStorage,
        RequestStack $stack,
        HttpKernelInterface $kernel
    ) {
        $this->competencyManager = $competencyManager;
        $this->objectiveManager = $objectiveManager;
        $this->tokenStorage = $tokenStorage;
        $this->request = $stack->getCurrentRequest();
        $this->kernel = $kernel;
    }

    /**
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenLearningObjectivesTool(OpenAdministrationToolEvent $event)
    {
        $this->competencyManager->ensureHasScale();
        $event->setData([]);
        $event->stopPropagation();
    }

    /**
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
