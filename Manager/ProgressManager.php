<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Activity\Evaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Progress\AbilityProgress;
use HeVinci\CompetencyBundle\Entity\Progress\CompetencyProgress;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.competency.progress_manager")
 */
class ProgressManager
{
    private $om;
    private $abilityRepo;
    private $competencyAbilityRepo;
    private $abilityProgressRepo;
    private $competencyProgressRepo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->abilityRepo = $om->getRepository('HeVinciCompetencyBundle:Ability');
        $this->competencyAbilityRepo = $om->getRepository('HeVinciCompetencyBundle:CompetencyAbility');
        $this->abilityProgressRepo = $om->getRepository('HeVinciCompetencyBundle:Progress\AbilityProgress');
        $this->competencyProgressRepo = $om->getRepository('HeVinciCompetencyBundle:Progress\CompetencyProgress');
    }

    /**
     * Computes and logs the progression of a user.
     *
     * @param Evaluation $evaluation
     */
    public function handleEvaluation(Evaluation $evaluation)
    {
        $activity = $evaluation->getActivityParameters()->getActivity();
        $abilities = $this->abilityRepo->findByActivity($activity);
        $user = $evaluation->getUser();

        foreach ($abilities as $ability) {
            $progress = $this->getAbilityProgress($ability, $user);

            if ($evaluation->isSuccessful() && !$progress->hasPassedActivity($activity)) {
                $progress->addPassedActivity($activity);

                if ($progress->getPassedActivityCount() >= $ability->getMinActivityCount()) {
                    $progress->setStatus(AbilityProgress::STATUS_ACQUIRED);
                } else {
                    $progress->setStatus(AbilityProgress::STATUS_PENDING);
                }

                $this->computeCompetencyProgress($ability, $user);
            }
        }

        $this->om->flush();
    }

    private function getAbilityProgress(Ability $ability, User $user)
    {
        $progress = $this->abilityProgressRepo->findOneBy([
            'ability' => $ability,
            'user' => $user
        ]);

        if (!$progress) {
            $progress = new AbilityProgress();
            $progress->setAbility($ability);
            $progress->setUser($user);
            $this->om->persist($progress);
        }

        return $progress;
    }

    private function computeCompetencyProgress(Ability $ability, User $user)
    {
        $competencyLinks = $this->competencyAbilityRepo->findBy(['ability' => $ability]);

        foreach ($competencyLinks as $link) {
            $progress = $this->getCompetencyProgress($link->getCompetency(), $user);
            $progress->setLevel($link->getLevel());
        }
    }

    private function getCompetencyProgress(Competency $competency, User $user)
    {
        $progress = $this->competencyProgressRepo->findOneBy([
            'competency' => $competency,
            'user' => $user,
            'type' => CompetencyProgress::TYPE_SUMMARY
        ]);

        if (!$progress) {
            $progress = new CompetencyProgress();
            $progress->setCompetency($competency);
            $progress->setUser($user);
            $progress->setType(CompetencyProgress::TYPE_SUMMARY);
            $this->om->persist($progress);
        }

        return $progress;
    }
}
