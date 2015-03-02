<?php

namespace HeVinci\CompetencyBundle\Tests\Repository;

use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class AbilityRepositoryTest extends RepositoryTestCase
{
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->repo = $this->om->getRepository('HeVinciCompetencyBundle:Ability');
    }

    public function testFindByFramework()
    {
        $scale = $this->persistScale('scale');
        $level = $this->persistLevel('l1', $scale);

        // framework 1
        $f1 = $this->persistCompetency('r1', null, $scale);
        $c1 = $this->persistCompetency('c1', $f1);
        $c2 = $this->persistCompetency('c2', $c1);
        $c3 = $this->persistCompetency('c3', $c2);
        $a1 = $this->persistAbility('a1');
        $a2 = $this->persistAbility('a2');
        $this->persistLink($c1, $a1, $level);
        $this->persistLink($c3, $a2, $level);

        // framework 2
        $f2 = $this->persistCompetency('r2', null, $scale);
        $a3 = $this->persistAbility('a3');
        $this->persistLink($f2, $a3, $level);

        $this->om->flush();

        $this->assertEquals(2, count($this->repo->findByFramework($f1)));
        $this->assertEquals(1, count($this->repo->findByFramework($f2)));
    }

    public function testDeleteOrphans()
    {
        $this->createLink(1);
        $this->createLink(2);
        $this->createLink(3);
        $this->persistAbility('Foo'); // not linked
        $this->om->flush();

        $this->assertEquals(4, $this->om->count('HeVinciCompetencyBundle:Ability'));
        $this->repo->deleteOrphans();
        $this->assertEquals(3, $this->om->count('HeVinciCompetencyBundle:Ability'));
    }

    private function createLink($index)
    {
        $competency = $this->persistCompetency('Competency ' . $index);
        $ability = $this->persistAbility('Ability ' . $index);
        $scale = $this->persistScale('Scale ' . $index);
        $level = $this->persistLevel('Level ' . $index, $scale, $index);
        $this->persistLink($competency, $ability, $level);
    }
}
