<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;
use HeVinci\CompetencyBundle\Entity\Objective;
use HeVinci\CompetencyBundle\Entity\Progress\AbilityProgress;
use HeVinci\CompetencyBundle\Entity\Progress\CompetencyProgress;
use HeVinci\CompetencyBundle\Entity\Progress\ObjectiveProgress;
use HeVinci\CompetencyBundle\Entity\Progress\UserProgress;

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

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->abilityRepo = $om->getRepository(Ability::class);
        $this->competencyRepo = $om->getRepository(Competency::class);
        $this->competencyAbilityRepo = $om->getRepository(CompetencyAbility::class);
        $this->objectiveRepo = $om->getRepository(Objective::class);
        $this->abilityProgressRepo = $om->getRepository(AbilityProgress::class);
        $this->competencyProgressRepo = $om->getRepository(CompetencyProgress::class);
        $this->objectiveProgressRepo = $om->getRepository(ObjectiveProgress::class);
        $this->userProgressRepo = $om->getRepository(UserProgress::class);
    }

    /**
     * Computes and logs the progression of a user.
     */
    public function handleEvaluation(ResourceUserEvaluation $evaluation)
    {
        $this->clearCache();
        $this->om->startFlushSuite();

        $resource = $evaluation->getResourceNode();
        $abilities = $this->abilityRepo->findByResource($resource);
        $user = $evaluation->getUser();

        foreach ($abilities as $ability) {
            $this->setCompetencyProgressResourceId($ability, $user, $resource->getId());
            $progress = $this->getAbilityProgress($ability, $user);

            if ($evaluation->isSuccessful()) {
                $progress->addPassedResource($resource);

                if (AbilityProgress::STATUS_ACQUIRED !== $progress->getStatus()) {
                    if ($progress->getPassedResourceCount() >= $ability->getMinResourceCount()) {
                        $progress->setStatus(AbilityProgress::STATUS_ACQUIRED);
                        $this->om->forceFlush();
                    } else {
                        $progress->setStatus(AbilityProgress::STATUS_PENDING);
                    }
                }

                $this->computeCompetencyProgress($ability, $user);
            } else {
                $progress->addFailedResource($resource);
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Recomputes the progression of a user or a group of users.
     * In case the subject is a user, returns the computed percentage,
     * otherwise returns null.
     *
     * Note: this method recomputes only the percentage of reached objectives.
     *
     * @param User|Group $subject
     *
     * @return int|null
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
            throw new \InvalidArgumentException('Subject must be an instance of User or Group');
        }

        $this->om->flush();

        return $percentage;
    }

    /**
     * Recomputes an objective progress for all the users and groups
     * of users to which that objective is assigned.
     *
     * Note: this method doesn't compute competency progress.
     */
    public function recomputeObjectiveProgress(Objective $objective)
    {
        $this->clearCache();
        $users = $this->objectiveRepo->findUsersWithObjective($objective);

        foreach ($users as $user) {
            $this->computeUserObjective($objective, $user);
            $this->computeUserProgress($user);
        }

        $this->om->flush();
    }

    /**
     * Returns user evaluation data for a given leaf competency.
     *
     * @return mixed
     */
    public function listLeafCompetencyLogs(Competency $competency, User $user)
    {
        return $this->abilityRepo->findEvaluationsByCompetency($competency, $user);
    }

    private function clearCache()
    {
        $this->cachedCompetencyProgresses = [];
        $this->cachedObjectiveProgresses = [];
        $this->cachedUserObjectives = null;
        $this->cachedUserProgress = null;
    }

    public function getAbilityProgress(Ability $ability, User $user)
    {
        $progress = $this->abilityProgressRepo->findOneBy([
            'ability' => $ability,
            'user' => $user,
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
            // search abilities of same level connected the competency
            $sameLevelAbilities = $this->abilityRepo->findByCompetencyAndLevel(
                $link->getCompetency(),
                $link->getLevel()
            );
            // search which ones have the status "acquired"
            $sameLevelAcquired = $this->abilityProgressRepo->findByAbilitiesAndStatus(
                $user,
                $sameLevelAbilities,
                AbilityProgress::STATUS_ACQUIRED
            );

            if (count($sameLevelAbilities) !== count($sameLevelAcquired)) {
                // if they're not all acquired, competency progress cannot be computed
                return;
            }

            $competency = $link->getCompetency();
            $progress = $this->getCompetencyProgress($competency, $user);
            $currentLevel = $progress->getLevel() ? $progress->getLevel()->getValue() : -1;

            if ($currentLevel >= $link->getLevel()->getValue()) {
                // we don't want to recompute pointlessly if the level
                // is the same than the current one, nor to decrease
                // the latter if it's inferior
                return;
            }

            $progress->setLevel($link->getLevel());
            $progress->setPercentage(100);

            $relatedCompetencies = $this->competencyRepo->findForProgressComputing($competency);

            $this->computeObjectives($progress);
            $this->computeParentCompetency($competency, $user, $relatedCompetencies);
        }
    }

    public function getCompetencyProgress(Competency $competency, User $user, $withLog = true)
    {
        if (!isset($this->cachedCompetencyProgresses[$competency->getId()])) {
            $progress = $this->competencyProgressRepo->findOneBy([
                'competency' => $competency,
                'user' => $user,
            ]);

            if (!$progress) {
                $progress = new CompetencyProgress();
                $progress->setCompetency($competency);
                $progress->setUser($user);
                $this->om->persist($progress);
            } elseif ($withLog) {
                $this->om->persist($progress->makeLog());
            }
            $this->om->flush();

            $this->cachedCompetencyProgresses[$competency->getId()] = $progress;
        }

        return $this->cachedCompetencyProgresses[$competency->getId()];
    }

    private function computeParentCompetency(Competency $startNode, User $user, array $related)
    {
        $parentNode = $this->getParentNode($startNode, $related);
        if (!$parentNode) {
            return;
        }

        $nodeProgress = $this->getCompetencyProgress($startNode, $user);
        $parentProgress = $this->getCompetencyProgress($parentNode, $user);
        $siblings = $this->getSiblingNodes($startNode, $parentNode, $related);

        $siblingCount = count($siblings);
        if (0 === $siblingCount) {
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

        return;
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
            if (0 === $competency->getLevel()) {
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
            $this->computeUserObjective($objectives[$i], $user);
        }

        if ($objectiveCount > 0) {
            $this->computeUserProgress($user);
        }
    }

    private function computeUserObjective(Objective $objective, User $user)
    {
        $objectiveProgress = $this->getObjectiveProgress($objective, $user);
        $links = $objective->getObjectiveCompetencies();
        $percentageSum = 0;

        for ($j = 0, $count = count($links); $j < $count; ++$j) {
            $competencyProgress = $this->getCompetencyProgress($links[$j]->getCompetency(), $user);
            $percentageSum += $competencyProgress->getPercentage();
        }

        $percentage = $count > 0 ? (int) ($percentageSum / $count) : 0;
        $objectiveProgress->setPercentage($percentage);

        return $percentage;
    }

    private function getObjectiveProgress(Objective $objective, User $user)
    {
        if (!isset($this->cachedObjectiveProgresses[$objective->getId()])) {
            $progress = $this->objectiveProgressRepo->findOneBy([
                'objective' => $objective,
                'user' => $user,
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

        $percentage = $count > 0 ? (int) ($percentageSum / $count) : 0;
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

    private function setCompetencyProgressResourceId(Ability $ability, User $user, $resourceId)
    {
        $competencyLinks = $this->competencyAbilityRepo->findBy(['ability' => $ability]);

        foreach ($competencyLinks as $link) {
            $competency = $link->getCompetency();
            $progress = $this->getCompetencyProgress($competency, $user);
            $progress->setResourceId($resourceId);
        }
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @return int
     */
    public function replaceUserProgressUser(User $from, User $to)
    {
        $userProgresses = $this->userProgressRepo->findByUser($from);

        if (count($userProgresses) > 0) {
            foreach ($userProgresses as $userProgress) {
                $userProgress->setUser($to);
            }

            $this->om->flush();
        }

        return count($userProgresses);
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @return int
     */
    public function replaceObjectiveProgressUser(User $from, User $to)
    {
        $objectiveProgresses = $this->objectiveProgressRepo->findByUser($from);

        if (count($objectiveProgresses) > 0) {
            foreach ($objectiveProgresses as $objectiveProgress) {
                $objectiveProgress->setUser($to);
            }

            $this->om->flush();
        }

        return count($objectiveProgresses);
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @return int
     */
    public function replaceCompetencyProgressUser(User $from, User $to)
    {
        $competencyProgresses = $this->competencyProgressRepo->findByUser($from);

        if (count($competencyProgresses) > 0) {
            foreach ($competencyProgresses as $competencyProgress) {
                $competencyProgress->setUser($to);
            }

            $this->om->flush();
        }

        return count($competencyProgresses);
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @return int
     */
    public function replaceAbilityProgressUser(User $from, User $to)
    {
        $abilityyProgresses = $this->abilityProgressRepo->findByUser($from);

        if (count($abilityyProgresses) > 0) {
            foreach ($abilityyProgresses as $abilityyProgress) {
                $abilityyProgress->setUser($to);
            }

            $this->om->flush();
        }

        return count($abilityyProgresses);
    }
}
