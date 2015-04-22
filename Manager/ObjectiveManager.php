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
    private $pagerFactory;
    private $objectiveRepo;
    private $competencyRepo;
    private $objectiveCompetencyRepo;

    /**
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "manager"        = @DI\Inject("hevinci.competency.competency_manager"),
     *     "pagerFactory"   = @DI\Inject("claroline.pager.pager_factory")

     * })
     *
     * @param ObjectManager     $om
     * @param CompetencyManager $manager
     * @param PagerFactory      $pagerFactory
     */
    public function __construct(
        ObjectManager $om,
        CompetencyManager $manager,
        PagerFactory $pagerFactory
    )
    {
        $this->om = $om;
        $this->competencyManager = $manager;
        $this->pagerFactory = $pagerFactory;
        $this->objectiveRepo = $om->getRepository('HeVinciCompetencyBundle:Objective');
        $this->competencyRepo = $om->getRepository('HeVinciCompetencyBundle:Competency');
        $this->objectiveCompetencyRepo = $om->getRepository('HeVinciCompetencyBundle:ObjectiveCompetency');
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
        $links = $objective->getObjectiveCompetencies();
        $result = [];

        foreach ($links as $link) {
            $loaded = $this->competencyManager->loadCompetency($link->getCompetency());
            $loaded['id'] = $link->getId(); // link is treated as the competency itself on client-side
            $loaded['framework'] = $link->getFramework()->getName();
            $loaded['level'] = $link->getLevel()->getName();
            $result[] = $loaded;
        }

        return $result;
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
    }

    /**
     * Returns a pager for all the users who have at least one objective.
     * If a particular objective is given, only the users who have that
     * objective are returned.
     *
     * @param Objective $objective
     * @return Pagerfanta
     */
    public function listUsersWithObjective(Objective $objective = null)
    {
        return $this->listSubjectsWithObjective('Users', $objective);
    }

    /**
     * Returns a pager for all the groups which have at least one objective.
     * If a particular objective is given, only the groups which have that
     * objective are returned.
     *
     * @param Objective $objective
     * @return Pagerfanta
     */
    public function listGroupsWithObjective(Objective $objective = null)
    {
        return $this->listSubjectsWithObjective('Groups', $objective);
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
     * Unassigns a user or group objective.
     *
     * @param Objective     $objective
     * @param User|Group    $subject
     * @return array
     * @throws \Exception if the subject is not an instance of User or Group
     */
    public function removeSubjectObjective(Objective $objective, $subject)
    {
        $target = $this->getSubjectType($subject);
        $entityMethod = "remove{$target}";
        $objective->{$entityMethod}($subject);
        $this->om->flush();
    }

    private function getSubjectType($subject)
    {
        if (!$subject instanceof User && !$subject instanceof Group) {
            throw new \Exception('Subject must be an instance of User or Group');
        }

        return $subject instanceof User ? 'User' : 'Group';
    }

    private function listSubjectsWithObjective($subjectType, Objective $objective = null)
    {
        $countMethod = "get{$subjectType}WithObjectiveCountQuery";
        $fetchMethod = "get{$subjectType}WithObjectiveQuery";
        $countQuery = $this->objectiveRepo->{$countMethod}($objective);
        $resultQuery = $this->objectiveRepo->{$fetchMethod}($objective);
        $adapter = new OrmArrayAdapter($countQuery, $resultQuery);

        return $this->pagerFactory->createPagerWithAdapter($adapter, 1);
    }
}
