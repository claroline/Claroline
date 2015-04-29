<?php

namespace HeVinci\CompetencyBundle\Util;

use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Activity\Evaluation;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;
use HeVinci\CompetencyBundle\Entity\Level;
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

    protected function persistAbility($name, $minActivityCount = 1)
    {
        $ability = new Ability();
        $ability->setName($name);
        $ability->setMinActivityCount($minActivityCount);
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
        $user->setMail($username . '@mail.com');
        $this->om->persist($user);

        return $user;
    }

    protected function persistActivity($name)
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
            $type = new ResourceType();
            $type->setName('activity');
            $this->om->persist($type);
            $this->defaults['activity_type'] = $type;
        }

        $node = new ResourceNode();
        $node->setName($name);
        $node->setCreator($this->defaults['user']);
        $node->setResourceType($this->defaults['activity_type']);
        $node->setWorkspace($this->defaults['workspace']);
        $node->setClass('foo');

        $activity = new Activity();
        $activity->setName($name);
        $activity->setDescription('desc');
        $activity->setResourceNode($node);

        $this->om->persist($node);
        $this->om->persist($activity);

        return $activity;
    }

    protected function persistEvaluation(Activity $activity, User $user, $status)
    {
        $params = new ActivityParameters();
        $params->setActivity($activity);

        $eval = new Evaluation();
        $eval->setActivityParameters($params);
        $eval->setUser($user);
        $eval->setStatus($status);

        $this->om->persist($params);
        $this->om->persist($eval);

        return $eval;
    }
}
