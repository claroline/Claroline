<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use HeVinci\CompetencyBundle\Manager\ObjectiveManager;
use HeVinci\CompetencyBundle\Manager\ProgressManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var ObjectiveManager */
    private $objectiveManager;

    /** @var ProgressManager */
    private $progressManager;

    /**
     * @DI\InjectParams({
     *     "objectiveManager" = @DI\Inject("hevinci.competency.objective_manager"),
     *     "progressManager"  = @DI\Inject("hevinci.competency.progress_manager")
     * })
     *
     * @param ObjectiveManager $objectiveManager
     * @param ProgressManager  $progressManager
     */
    public function __construct(ObjectiveManager $objectiveManager, ProgressManager $progressManager)
    {
        $this->objectiveManager = $objectiveManager;
        $this->progressManager = $progressManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Objective nodes
        $objectiveCount = $this->objectiveManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[HeVinciCompetencyBundle] updated Objective count: $objectiveCount");

        // Replace user of UserProgress nodes
        $userProgressCount = $this->progressManager->replaceUserProgressUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[HeVinciCompetencyBundle] updated UserProgress count: $userProgressCount");

        // Replace user of ObjectiveProgress nodes
        $objectiveProgressCount = $this->progressManager->replaceObjectiveProgressUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[HeVinciCompetencyBundle] updated ObjectiveProgress count: $objectiveProgressCount");

        // Replace user of CompetencyProgress nodes
        $competencyProgress = $this->progressManager->replaceCompetencyProgressUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[HeVinciCompetencyBundle] updated CompetencyProgress count: $competencyProgress");

        // Replace user of AbilityProgress nodes
        $abilityProgress = $this->progressManager->replaceAbilityProgressUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[HeVinciCompetencyBundle] updated AbilityProgress count: $abilityProgress");
    }
}
