<?php

namespace HeVinci\CompetencyBundle\Util;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Objective;
use HeVinci\CompetencyBundle\Entity\ObjectiveCompetency;
use HeVinci\CompetencyBundle\Entity\Progress\AbilityProgress;
use HeVinci\CompetencyBundle\Entity\Progress\CompetencyProgress;
use HeVinci\CompetencyBundle\Entity\Scale;

abstract class RepositoryTestCase extends TransactionalTestCase
{
    protected $om;
    private $defaults = [];

    protected function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    protected function persistCompetency($name, Competency $parent = null, Scale $scale = null)
    {
        $competency = new Competency();
        $competency->setName($name);

        if ($parent) {
            $competency->setParent($parent);
        }

        if ($scale) {
            $competency->setScale($scale);
        }

        $this->om->persist($competency);

        return $competency;
    }

    protected function persistAbility($name, $minResourceCount = 1)
    {
        $ability = new Ability();
        $ability->setName($name);
        $ability->setMinResourceCount($minResourceCount);
        $this->om->persist($ability);

        return $ability;
    }

    protected function persistScale($name)
    {
        $scale = new Scale();
        $scale->setName($name);
        $this->om->persist($scale);

        return $scale;
    }

    protected function persistLevel($name, Scale $scale, $value = 0)
    {
        $level = new Level();
        $level->setName($name);
        $level->setValue($value);
        $level->setScale($scale);
        $scale->addLevel($level);
        $this->om->persist($level);

        return $level;
    }

    protected function persistLink(Competency $competency, Ability $ability, Level $level)
    {
        $link = new CompetencyAbility();
        $link->setCompetency($competency);
        $link->setAbility($ability);
        $link->setLevel($level);
        $this->om->persist($link);

        return $link;
    }

    protected function persistUser($username)
    {
        $user = new User();
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setUsername($username);
        $user->setPassword($username);
        $user->setEmail($username.'@email.com');
        $this->om->persist($user);

        return $user;
    }

    protected function persistGroup($name)
    {
        $group = new Group();
        $group->setName($name);
        $this->om->persist($group);

        return $group;
    }

    protected function persistResource($name)
    {
        if (!isset($this->defaults['user'])) {
            $this->defaults['user'] = $this->persistUser('default_user');
        }

        if (!isset($this->defaults['workspace'])) {
            $workspace = new Workspace();
            $workspace->setName('ws-jdoe');
            $workspace->setCreator($this->defaults['user']);
            $workspace->setCode('jdoe-123');
            $workspace->setGuid('123');
            $this->om->persist($workspace);
            $this->defaults['workspace'] = $workspace;
        }

        if (!isset($this->defaults['activity_type'])) {
            $type = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('activity');
            $this->defaults['activity_type'] = $type;
        }

        $node = new ResourceNode();
        $node->setName($name);
        $node->setCreator($this->defaults['user']);
        $node->setResourceType($this->defaults['activity_type']);
        $node->setWorkspace($this->defaults['workspace']);
        $node->setGuid($name);
        $node->setClass('foo');

        $this->om->persist($node);

        return $node;
    }

    protected function persistEvaluation(
        ResourceNode $resource,
        User $user,
        $status,
        ResourceEvaluation $previous = null
    ) {
        $eval = $previous ? $previous->getResourceUserEvaluation() : new ResourceUserEvaluation();
        $eval->setResourceNode($resource);
        $eval->setUser($user);
        $eval->setUserName($user->getUsername());
        $eval->setStatus($status);
        $eval->setDate(new \DateTime());
        $this->om->persist($eval);

        $pastEval = $previous ?: new ResourceEvaluation();
        $pastEval->setResourceUserEvaluation($eval);
        $pastEval->setStatus($status);
        $this->om->persist($pastEval);

        return $pastEval;
    }

    protected function persistObjective($name, array $competenciesData)
    {
        $objective = new Objective();
        $objective->setName($name);

        foreach ($competenciesData as $competencyData) {
            $link = new ObjectiveCompetency();
            $link->setCompetency($competencyData[0]);
            $link->setFramework($competencyData[1]);
            $link->setLevel($competencyData[2]);
            $this->om->persist($link);
            $objective->addObjectiveCompetency($link);
        }

        $this->om->persist($objective);

        return $objective;
    }

    protected function persistCompetencyProgress(User $user, Competency $competency)
    {
        $progress = new CompetencyProgress();
        $progress->setUser($user);
        $progress->setCompetency($competency);
        $this->om->persist($progress);

        return $progress;
    }

    protected function persistAbilityProgress(User $user, Ability $ability, $status)
    {
        $progress = new AbilityProgress();
        $progress->setUser($user);
        $progress->setAbility($ability);
        $progress->setStatus($status);
        $this->om->persist($progress);

        return $progress;
    }
}
