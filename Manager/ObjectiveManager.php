<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Adapter\OrmArrayAdapter;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Objective;
use HeVinci\CompetencyBundle\Entity\ObjectiveCompetency;
use JMS\DiExtraBundle\Annotation as DI;
use Pagerfanta\Pagerfanta;

/**
 * @DI\Service("hevinci.competency.objective_manager")
 */
class ObjectiveManager
{
    private $om;
    private $competencyManager;
    private $progressManager;
    private $pagerFactory;
    private $objectiveRepo;
    private $competencyRepo;
    private $objectiveCompetencyRepo;
    private $competencyProgressRepo;

    /**
     * @DI\InjectParams({
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "competencyManager"  = @DI\Inject("hevinci.competency.competency_manager"),
     *     "progressManager"    = @DI\Inject("hevinci.competency.progress_manager"),
     *     "pagerFactory"       = @DI\Inject("claroline.pager.pager_factory"),
     * })
     *
     * @param ObjectManager     $om
     * @param CompetencyManager $competencyManager
     * @param ProgressManager   $progressManager
     * @param PagerFactory      $pagerFactory
     */
    public function __construct(
        ObjectManager $om,
        CompetencyManager $competencyManager,
        ProgressManager $progressManager,
        PagerFactory $pagerFactory
    )
    {
        $this->om = $om;
        $this->competencyManager = $competencyManager;
        $this->progressManager = $progressManager;
        $this->pagerFactory = $pagerFactory;
        $this->objectiveRepo = $om->getRepository('HeVinciCompetencyBundle:Objective');
        $this->competencyRepo = $om->getRepository('HeVinciCompetencyBundle:Competency');
        $this->objectiveCompetencyRepo = $om->getRepository('HeVinciCompetencyBundle:ObjectiveCompetency');
        $this->competencyProgressRepo = $om->getRepository('HeVinciCompetencyBundle:Progress\CompetencyProgress');
    }

    /**
     * Persists a learning objective.
     *
     * @param Objective $objective
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
     * @param Objective $objective
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
     * @param Objective $objective
     * @param User      $user
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
                function ($collection) use ($user, $competencyProgressesById) {
                    if (isset($collection['id'])) {
                        $id = isset($collection['originalId']) ? $collection['originalId'] : $collection['id'];

                        if (isset($competencyProgressesById[$id])) {
                            $progress = $competencyProgressesById[$id];
                            $collection['progress'] = $progress->getPercentage();

                            if ($level = $progress->getLevel()) {
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
     *
     * @param Objective $objective
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
     * @param Objective     $objective
     * @param Competency    $competency
     * @param Level         $level
     * @return mixed array|bool
     * @throws \LogicException if the level doesn't belong to the root competency scale
     */
    public function linkCompetency(Objective $objective, Competency $competency, Level $level)
    {
        $link = $this->objectiveCompetencyRepo->findOneBy([
            'competency' => $competency,
            'objective' => $objective
        ]);

        if ($link) {
            return false;
        }

        $framework = $this->competencyRepo->findOneBy(['root' => $competency->getRoot()]);

        if ($level->getScale() !== $framework->getScale()) {
            throw new \LogicException(
                'Objective level must belong to the root competency scale'
            );
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
     *
     * @param ObjectiveCompetency $link
     */
    public function deleteCompetencyLink(ObjectiveCompetency $link)
    {
        $this->om->remove($link);
        $this->om->flush();
        $this->progressManager->recomputeObjectiveProgress($link->getObjective());
    }

    /**
     * Returns a pager for all the users who have at least one objective.
     * If a particular objective is given, only the users who have that
     * objective are returned.
     *
     * @param Objective $objective
     * @param int       $page
     * @return Pagerfanta
     */
    public function listUsersWithObjective(Objective $objective = null, $page = 1)
    {
        return $this->listSubjectsWithObjective('Users', $objective, $page);
    }

    /**
     * Returns a pager for all the groups which have at least one objective.
     * If a particular objective is given, only the groups which have that
     * objective are returned.
     *
     * @param Objective $objective
     * @param int       $page
     * @return Pagerfanta
     */
    public function listGroupsWithObjective(Objective $objective = null, $page = 1)
    {
        return $this->listSubjectsWithObjective('Groups', $objective, $page);
    }

    /**
     * Returns a pager for all the members of a group, including progress data.
     *
     * @param Group $group
     * @param int   $page
     * @return Pagerfanta
     */
    public function listGroupUsers(Group $group, $page = 1)
    {
        $countQuery = $this->objectiveRepo->getGroupUsersCountQuery($group);
        $resultQuery = $this->objectiveRepo->getGroupUsersQuery($group);
        $adapter = new OrmArrayAdapter($countQuery, $resultQuery);

        return $this->pagerFactory->createPagerWithAdapter($adapter, $page);
    }

    /**
     * Assigns an objective to a user or a group. If the objective has already
     * been assigned, returns false. Otherwise, returns true.
     *
     * @param Objective     $objective
     * @param User|Group    $subject
     * @return bool
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
     * @return array
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
     * @param Objective $objective
     * @param Group     $group
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
     * @param Objective $objective
     * @param User $user
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

    private function listSubjectsWithObjective($subjectType, Objective $objective = null, $page = 1)
    {
        $countMethod = "get{$subjectType}WithObjectiveCountQuery";
        $fetchMethod = "get{$subjectType}WithObjectiveQuery";
        $countQuery = $this->objectiveRepo->{$countMethod}($objective);
        $resultQuery = $this->objectiveRepo->{$fetchMethod}($objective);
        $adapter = new OrmArrayAdapter($countQuery, $resultQuery);

        return $this->pagerFactory->createPagerWithAdapter($adapter, $page);
    }
}
