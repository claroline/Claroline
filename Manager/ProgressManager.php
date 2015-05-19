<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Activity\Evaluation;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Objective;
use HeVinci\CompetencyBundle\Entity\Progress\AbilityProgress;
use HeVinci\CompetencyBundle\Entity\Progress\CompetencyProgress;
use HeVinci\CompetencyBundle\Entity\Progress\ObjectiveProgress;
use HeVinci\CompetencyBundle\Entity\Progress\UserProgress;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.competency.progress_manager")
 */
class ProgressManager
{
    private $om;
    private $abilityRepo;
    private $competencyRepo;
    private $competencyAbilityRepo;
    private $objectiveRepo;
    private $abilityProgressRepo;
    private $competencyProgressRepo;
    private $objectiveProgressRepo;
    private $userProgressRepo;
    private $cachedCompetencyProgresses = [];
    private $cachedObjectiveProgresses = [];
    private $cachedUserObjectives = null;
    private $cachedUserProgress = null;

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
        $this->competencyRepo = $om->getRepository('HeVinciCompetencyBundle:Competency');
        $this->competencyAbilityRepo = $om->getRepository('HeVinciCompetencyBundle:CompetencyAbility');
        $this->objectiveRepo = $om->getRepository('HeVinciCompetencyBundle:Objective');
        $this->abilityProgressRepo = $om->getRepository('HeVinciCompetencyBundle:Progress\AbilityProgress');
        $this->competencyProgressRepo = $om->getRepository('HeVinciCompetencyBundle:Progress\CompetencyProgress');
        $this->objectiveProgressRepo = $om->getRepository('HeVinciCompetencyBundle:Progress\ObjectiveProgress');
        $this->userProgressRepo = $om->getRepository('HeVinciCompetencyBundle:Progress\UserProgress');
    }

    /**
     * Computes and logs the progression of a user.
     *
     * @param Evaluation $evaluation
     */
    public function handleEvaluation(Evaluation $evaluation)
    {
        $this->clearCache();

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

    /**
     * Recomputes the progression of a user or a group of users.
     * In case the subject is a user, returns the computed percentage,
     * otherwise returns null.
     *
     * Note: this method recomputes only the percentage of reached objectives.
     *
     * @param User|Group $subject
     * @return null|int
     */
    public function recomputeUserProgress($subject)
    {
        $this->clearCache();
        $percentage = null;

        if ($subject instanceof User) {
            $percentage = $this->computeUserProgress($subject);
        } elseif ($subject instanceof Group) {
            foreach ($subject->getUsers() as $user) {
                $this->computeUserProgress($user);
            }
        } else {
            throw new \InvalidArgumentException(
                'Subject must be an instance of User or Group'
            );
        }

        $this->om->flush();

        return $percentage;
    }

    private function clearCache()
    {
        $this->cachedCompetencyProgresses = [];
        $this->cachedObjectiveProgresses = [];
        $this->cachedUserObjectives = null;
        $this->cachedUserProgress = null;
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
            $competency = $link->getCompetency();
            $progress = $this->getCompetencyProgress($competency, $user);
            $progress->setLevel($link->getLevel());
            $progress->setPercentage(100);

            $relatedCompetencies = $this->competencyRepo->findForProgressComputing($competency);

            $this->computeObjectives($progress);
            $this->computeParentCompetency($competency, $user, $relatedCompetencies);
        }
    }

    private function getCompetencyProgress(Competency $competency, User $user)
    {
        if (!isset($this->cachedCompetencyProgresses[$competency->getId()])) {
            $progress = $this->competencyProgressRepo->findOneBy([
                'competency' => $competency,
                'user' => $user
            ]);

            if (!$progress) {
                $progress = new CompetencyProgress();
                $progress->setCompetency($competency);
                $progress->setUser($user);
                $this->om->persist($progress);
            } else {
                $this->om->persist($progress->makeLog());
            }

            $this->cachedCompetencyProgresses[$competency->getId()] = $progress;
        }

        return $this->cachedCompetencyProgresses[$competency->getId()];
    }

    private function computeParentCompetency(Competency $startNode, User $user, array $related)
    {
        if (!($parentNode = $this->getParentNode($startNode, $related))) {
            return;
        }

        $nodeProgress = $this->getCompetencyProgress($startNode, $user);
        $parentProgress = $this->getCompetencyProgress($parentNode, $user);
        $siblings = $this->getSiblingNodes($startNode, $parentNode, $related);

        if (0 === $siblingCount = count($siblings)) {
            $parentProgress->setPercentage($nodeProgress->getPercentage());
            $parentProgress->setLevel($nodeProgress->getLevel());
        } else {
            $percentageSum = $nodeProgress->getPercentage();
            $levelSum = $nodeProgress->getLevel()->getValue();
            $levelTerms = 1;

            foreach ($siblings as $sibling) {
                $siblingProgress = $this->getCompetencyProgress($sibling, $user);
                $percentageSum += $siblingProgress->getPercentage();
                $siblingLevel = $siblingProgress->getLevel();
                $levelSum += $siblingLevel ? $siblingLevel->getValue() : 0;
                $levelTerms += $siblingLevel ? 1 : 0;
            }

            $parentProgress->setPercentage((int) ($percentageSum / ($siblingCount + 1)));
            $parentProgress->setLevel($this->getLevel((int) ($levelSum / $levelTerms), $related));
        }

        $this->computeObjectives($parentProgress);
        $this->computeParentCompetency($parentNode, $user, $related);
    }

    private function getParentNode(Competency $startNode, array $related)
    {
        foreach ($related as $node) {
            if ($node->getLevel() === $startNode->getLevel() - 1
                && $node->getLeft() < $startNode->getLeft()
                && $node->getRight() > $startNode->getRight()) {
                return $node;
            }
        }

        return null;
    }

    private function getSiblingNodes(Competency $startNode, Competency $parent, array $related)
    {
        return array_filter($related, function ($node) use ($startNode, $parent) {
            return $node !== $startNode
                && $node->getLevel() === $startNode->getLevel()
                && $node->getLeft() > $parent->getLeft()
                && $node->getRight() < $parent->getRight();
        });
    }

    private function getLevel($value, array $related)
    {
        $root = null;

        foreach ($related as $competency) {
            if ($competency->getLevel() === 0) {
                $root = $competency;
                break;
            }
        }

        if (!$root) {
            throw new \Exception('Cannot find root competency in related nodes');
        }

        foreach ($root->getScale()->getLevels() as $level) {
            if ($level->getValue() === $value) {
                return $level;
            }
        }

        throw new \Exception("Cannot find level with value {$value}");
    }

    private function computeObjectives(CompetencyProgress $progress)
    {
        // only a fully acquired competency can make the objectives progress vary
        if ($progress->getPercentage() < 100) {
            return;
        }

        $competency = $progress->getCompetency();
        $user = $progress->getUser();

        // find linked objectives
        $objectives = $this->objectiveRepo->findByCompetencyAndUser($competency, $user);

        // remove objectives for which the acquired competency level is insufficient
        $objectives = array_filter($objectives, function ($objective) use ($progress, $competency) {
            foreach ($objective->getObjectiveCompetencies() as $link) {
                if ($link->getCompetency() === $competency
                    && $link->getLevel()->getValue() > $progress->getLevel()->getValue()) {
                    return false;
                }
            }

            return true;
        });

        // compute each objective percentage
        for ($i = 0, $objectiveCount = count($objectives); $i < $objectiveCount; ++$i) {
            $objectiveProgress = $this->getObjectiveProgress($objectives[$i], $user);
            $links = $objectives[$i]->getObjectiveCompetencies();
            $percentageSum = 0;

            for ($j = 0, $count = count($links); $j < $count; ++$j) {
                $competencyProgress = $this->getCompetencyProgress($links[$j]->getCompetency(), $user);
                $percentageSum += $competencyProgress->getPercentage();
            }

            $objectiveProgress->setPercentage((int) ($percentageSum / $count));
        }

        if ($objectiveCount > 0) {
            $this->computeUserProgress($user);
        }
    }

    private function getObjectiveProgress(Objective $objective, User $user)
    {
        if (!isset($this->cachedObjectiveProgresses[$objective->getId()])) {
            $progress = $this->objectiveProgressRepo->findOneBy([
                'objective' => $objective,
                'user' => $user
            ]);

            if (!$progress) {
                $progress = new ObjectiveProgress();
                $progress->setObjective($objective);
                $progress->setUser($user);
                $this->om->persist($progress);
            } else {
                $this->om->persist($progress->makeLog());
            }

            $this->cachedObjectiveProgresses[$objective->getId()] = $progress;
        }

        return $this->cachedObjectiveProgresses[$objective->getId()];
    }

    private function computeUserProgress(User $user)
    {
        $progress = $this->getUserProgress($user);
        $objectives = $this->getUserObjectives($user);
        $percentageSum = 0;
        for ($i = 0, $count = count($objectives); $i < $count; ++$i) {
            $objectiveProgress = $this->getObjectiveProgress($objectives[$i], $user);
            $percentageSum += $objectiveProgress->getPercentage();
        }

        $percentage = (int) ($percentageSum / $count);
        $progress->setPercentage($percentage);

        return $percentage;
    }

    private function getUserObjectives(User $user)
    {
        if (!$this->cachedUserObjectives) {
            $this->cachedUserObjectives = $this->objectiveRepo->findByUser($user, false);
        }

        return $this->cachedUserObjectives;
    }

    private function getUserProgress(User $user)
    {
        if (!$this->cachedUserProgress) {
            $progress = $this->userProgressRepo->findOneBy(['user' => $user]);

            if (!$progress) {
                $progress = new UserProgress();
                $progress->setUser($user);
                $this->om->persist($progress);
            } else {
                $this->om->persist($progress->makeLog());
            }

            $this->cachedUserProgress = $progress;
        }

        return $this->cachedUserProgress;
    }
}
