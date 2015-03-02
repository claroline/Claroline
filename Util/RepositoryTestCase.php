<?php

namespace HeVinci\CompetencyBundle\Util;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Scale;

class RepositoryTestCase extends TransactionalTestCase
{
    protected $om;

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

    protected function persistAbility($name)
    {
        $ability = new Ability();
        $ability->setName($name);
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
}
