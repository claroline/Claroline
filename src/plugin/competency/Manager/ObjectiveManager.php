<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Objective;
use HeVinci\CompetencyBundle\Entity\ObjectiveCompetency;
use HeVinci\CompetencyBundle\Entity\Progress\AbilityProgress;
use HeVinci\CompetencyBundle\Entity\Progress\CompetencyProgress;
use Symfony\Component\Translation\TranslatorInterface;

class ObjectiveManager
{
    private $om;
    private $competencyManager;
    private $progressManager;
    private $objectiveRepo;
    private $competencyRepo;
    private $objectiveCompetencyRepo;
    private $competencyProgressRepo;
    private $translator;

    public function __construct(
        ObjectManager $om,
        CompetencyManager $competencyManager,
        ProgressManager $progressManager,
        TranslatorInterface $translator
    ) {
        $this->om = $om;
        $this->competencyManager = $competencyManager;
        $this->progressManager = $progressManager;
        $this->objectiveRepo = $om->getRepository(Objective::class);
        $this->competencyRepo = $om->getRepository(Competency::class);
        $this->objectiveCompetencyRepo = $om->getRepository(ObjectiveCompetency::class);
        $this->competencyProgressRepo = $om->getRepository(CompetencyProgress::class);
        $this->translator = $translator;
    }

    /**
     * Persists a learning objective.
     *
     * @return Objective
     */
    public function persistObjective(Objective $objective)
    {
        $this->om->persist($objective);
        $this->om->flush();

        return $objective;
    }

    /**
     * Returns the list of existing objectives.
     */
    public function listObjectives()
    {
        return $this->objectiveRepo->findWithCompetencyCount();
    }

    /**
     * Returns an array representation of all the competencies
     * associated with an objective, including sub-competencies
     * and abilities, if any.
     *
     * @return array
     */
    public function loadObjectiveCompetencies(Objective $objective)
    {
        return $this->doLoadObjectiveCompetencies($objective, true);
    }

    /**
     * Returns an array representation of all the competencies associated
     * with a user objective, including sub-competencies and progress data
     * at each level.
     *
     * @return array
     */
    public function loadUserObjectiveCompetencies(Objective $objective, User $user)
    {
        $competencies = $this->doLoadObjectiveCompetencies($objective, false);
        $competenciesWithProgress = [];
        $competencyIds = [];

        // extract the competency ids from the original nested array
        $this->competencyManager->walkCollection($competencies, function ($competency) use (&$competencyIds) {
            if (isset($competency['id'])) {
                $competencyIds[] = isset($competency['originalId']) ?
                    $competency['originalId'] :
                    $competency['id'];
            }

            return $competency;
        });

        // fetch competency progress entities in one query and sort them by id
        $competencyProgresses = $this->competencyProgressRepo->findByUserAndCompetencyIds($user, $competencyIds);
        $competencyProgressesById = [];

        foreach ($competencyProgresses as $competencyProgress) {
            $competencyProgressesById[$competencyProgress->getCompetency()->getId()] = $competencyProgress;
        }

        // augment the original array with progress data
        foreach ($competencies as $competency) {
            $competenciesWithProgress[] = $this->competencyManager->walkCollection(
                $competency,
                function ($collection) use ($competencyProgressesById) {
                    if (isset($collection['id'])) {
                        $id = isset($collection['originalId']) ? $collection['originalId'] : $collection['id'];

                        if (isset($competencyProgressesById[$id])) {
                            $progress = $competencyProgressesById[$id];
                            $collection['progress'] = $progress->getPercentage();
                            $collection['latestResource'] = $progress->getResourceId();

                            $level = $progress->getLevel();
                            if ($level) {
                                $collection['userLevel'] = $level->getName();
                                $collection['userLevelValue'] = $level->getValue();
                            }
                        }
                    }

                    return $collection;
                }
            );
        }

        return $competenciesWithProgress;
    }

    /**
     * Deletes an objective.
     */
    public function deleteObjective(Objective $objective)
    {
        $this->om->remove($objective);
        $this->om->flush();
    }

    /**
     * Creates an association between an objective and a competency,
     * with an expected level. Returns a full array representation of
     * the newly associated competency if the link doesn't already exist.
     * Otherwise, returns false.
     *
     * @return mixed array|bool
     *
     * @throws \LogicException if the level doesn't belong to the root competency scale
     */
    public function linkCompetency(Objective $objective, Competency $competency, Level $level)
    {
        $link = $this->objectiveCompetencyRepo->findOneBy([
            'competency' => $competency,
            'objective' => $objective,
        ]);

        if ($link) {
            return false;
        }

        $framework = $this->competencyRepo->findOneBy(['root' => $competency->getRoot()]);

        if ($level->getScale() !== $framework->getScale()) {
            throw new \LogicException('Objective level must belong to the root competency scale');
        }

        $link = new ObjectiveCompetency();
        $link->setObjective($objective);
        $link->setCompetency($competency);
        $link->setLevel($level);
        $link->setFramework($framework);

        $this->om->persist($link);
        $this->om->flush();

        $this->progressManager->recomputeObjectiveProgress($objective);

        $competency = $this->competencyManager->loadCompetency($competency);
        $competency['id'] = $link->getId(); // link is treated as the competency itself on client-side
        $competency['framework'] = $framework->getName();
        $competency['level'] = $level->getName();

        return $competency;
    }

    /**
     * Deletes a link between an objective and a competency.
     */
    public function deleteCompetencyLink(ObjectiveCompetency $link)
    {
        $this->om->remove($link);
        $this->om->flush();
        $this->progressManager->recomputeObjectiveProgress($link->getObjective());
    }

    /**
     * Assigns an objective to a user or a group. If the objective has already
     * been assigned, returns false. Otherwise, returns true.
     *
     * @param User|Group $subject
     *
     * @return bool
     *
     * @throws \Exception if the subject is not an instance of User or Group
     */
    public function assignObjective(Objective $objective, $subject)
    {
        $target = $this->getSubjectType($subject);
        $hasMethod = "has{$target}";
        $addMethod = "add{$target}";

        if ($objective->{$hasMethod}($subject)) {
            return false;
        }

        $objective->{$addMethod}($subject);
        $this->om->flush();

        $this->progressManager->recomputeUserProgress($subject);

        return true;
    }

    /**
     * Returns an array representation of the objectives assigned to a user or a group.
     *
     * @param User|Group $subject
     *
     * @return array
     *
     * @throws \Exception if the subject is not an instance of User or Group
     */
    public function loadSubjectObjectives($subject)
    {
        $target = $this->getSubjectType($subject);
        $repoMethod = "findBy{$target}";

        return $this->objectiveRepo->{$repoMethod}($subject);
    }

    /**
     * Removes a group objective.
     *
     * @return array
     */
    public function removeGroupObjective(Objective $objective, Group $group)
    {
        $objective->removeGroup($group);
        $this->om->flush();
        $this->progressManager->recomputeUserProgress($group);
    }

    /**
     * Removes a user objective. If the objective is not specifically assigned to
     * the user (e.g. coming from a group), return false. Otherwise, returns the
     * re-computed percentage of user progression.
     *
     * @return bool|int
     */
    public function removeUserObjective(Objective $objective, User $user)
    {
        if (!$objective->hasUser($user)) {
            return false;
        }

        $objective->removeUser($user);
        $this->om->flush();

        return $this->progressManager->recomputeUserProgress($user);
    }

    /**
     * Retrieves an objective object by its id.
     *
     * @param int $objectiveId
     *
     * @return Objective|null
     */
    public function getObjectiveById($objectiveId)
    {
        return $this->objectiveRepo->findOneById($objectiveId);
    }

    public function getCompetencyFinalChildren(array $competency, &$list, $requiredLevel = 0, $nbLevels = 1)
    {
        if (isset($competency['__children']) && count($competency['__children']) > 0) {
            foreach ($competency['__children'] as $child) {
                self::getCompetencyFinalChildren($child, $list, $requiredLevel, $nbLevels);
            }
        } else {
            $competency['requiredLevel'] = $requiredLevel;
            $competency['nbLevels'] = $nbLevels;

            if (!isset($list[$competency['id']]) || $list[$competency['id']]['requiredLevel'] < $requiredLevel) {
                $list[$competency['id']] = $competency;
            }
        }
    }

    private function doLoadObjectiveCompetencies(Objective $objective, $loadAbilities)
    {
        $links = $objective->getObjectiveCompetencies();
        $competencies = [];

        foreach ($links as $link) {
            $competency = $this->competencyManager->loadCompetency($link->getCompetency(), $loadAbilities);
            $competency['originalId'] = $competency['id'];
            $competency['id'] = $link->getId(); // link is treated as the competency itself on client-side
            $competency['framework'] = $link->getFramework()->getName();
            $competency['level'] = $link->getLevel()->getName();
            $competency['levelValue'] = $link->getLevel()->getValue();
            $competency['nbLevels'] = count($link->getLevel()->getScale()->getLevels());
            $competencies[] = $competency;
        }

        return $competencies;
    }

    private function getSubjectType($subject)
    {
        if (!$subject instanceof User && !$subject instanceof Group) {
            throw new \Exception('Subject must be an instance of User or Group');
        }

        return $subject instanceof User ? 'User' : 'Group';
    }

    public function getUserChallengeByLevel(User $user, Competency $competency, $level)
    {
        $rootComptency = empty($competency->getParent()) ?
            $competency :
            $this->competencyManager->getCompetencyById($competency->getRoot());
        $scale = $rootComptency->getScale();
        $nbPassed = 0;
        $nbToPass = 0;
        $challengeError = null;
        $levelEntity = $this->competencyManager->getLevelByScaleAndValue($scale, $level);
        $caLinks = $this->competencyManager->getCompetencyAbilityLinksByCompetencyAndLevel($competency, $levelEntity);

        foreach ($caLinks as $link) {
            $ability = $link->getAbility();
            $abilityProgress = $this->progressManager->getAbilityProgress($ability, $user);
            $target = $ability->getMinResourceCount();
            $passed = $abilityProgress->getPassedResourceCount();
            $nbToPass += $target;
            $nbPassed += $passed >= $target ? $target : $passed;
            $resources = $ability->getResources();
            $nbValidResources = 0;

            foreach ($resources as $resource) {
                if ('ujm_exercise' === $resource->getResourceType()->getName()) {
                    ++$nbValidResources;
                }
            }
            if (0 === $target || $nbValidResources < $target) {
                $challengeError = $this->translator->trans('objective.invalid_challenge_msg', [], 'competency');
            }
        }

        return [
            'nbPassed' => $nbPassed,
            'nbToPass' => $nbToPass,
            'error' => $challengeError,
        ];
    }

    public function getRelevantResourceForUserByLevel(User $user, Competency $competency, Level $level)
    {
        $allResources = [];
        $passedResources = [];
        $failedResources = [];
        $toDoResources = [];
        $resource = null;
        $links = $this->competencyManager->getCompetencyAbilityLinksByCompetencyAndLevel($competency, $level);

        foreach ($links as $link) {
            $ability = $link->getAbility();
            $abilityProgress = $this->progressManager->getAbilityProgress($ability, $user);
            $resources = $ability->getResources();

            foreach ($resources as $resource) {
                if ($this->isValidResource($resource)) {
                    $allResources[$resource->getId()] = $resource;

                    if (AbilityProgress::STATUS_ACQUIRED === $abilityProgress->getStatus() ||
                        $abilityProgress->hasPassedResource($resource)
                    ) {
                        $passedResources[$resource->getId()] = $resource;
                    } elseif ($abilityProgress->hasFailedResource($resource)) {
                        $failedResources[$resource->getId()] = $resource;
                    } else {
                        $toDoResources[$resource->getId()] = $resource;
                    }
                }
            }
        }
        if (count($toDoResources) > 0) {
            $index = mt_rand(0, count($toDoResources) - 1);
            $resource = array_values($toDoResources)[$index];
        } elseif (count($failedResources) > 0) {
            $index = mt_rand(0, count($failedResources) - 1);
            $resource = array_values($failedResources)[$index];
        } elseif (count($allResources) > 0) {
            $index = mt_rand(0, count($allResources) - 1);
            $resource = array_values($allResources)[$index];
        }

        return $resource;
    }

    public function isValidResource(ResourceNode $resource)
    {
        $type = $resource->getResourceType()->getName();

        return 'ujm_exercise' === $type;
    }

    /**
     * Find all content for a given user and replace him by another.
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $objectives = $this->objectiveRepo->findAllByUser($from);

        if (count($objectives) > 0) {
            foreach ($objectives as $objective) {
                $objective->removeUser($from);
                $objectives->addUser($to);
            }

            $this->om->flush();
        }

        return count($objectives);
    }
}
