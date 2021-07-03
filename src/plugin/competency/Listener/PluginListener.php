<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use HeVinci\CompetencyBundle\Manager\ObjectiveManager;
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

    public function onOpenLearningObjectivesTool(OpenToolEvent $event)
    {
        $this->competencyManager->ensureHasScale();
        $event->setData([]);
        $event->stopPropagation();
    }

    public function onOpenMyLearningObjectivesTool(OpenToolEvent $event)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $objectives = $user instanceof User ? $this->objectiveManager->loadSubjectObjectives($user) : [];
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
}
