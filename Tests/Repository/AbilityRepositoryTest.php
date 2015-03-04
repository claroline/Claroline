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

    public function testFindFirstByName()
    {
        $level = $this->persistLevel('l1', $this->persistScale('scale'));

        // framework 1
        $f1 = $this->persistCompetency('f1');
        $a1 = $this->persistAbility('FOO');
        $this->persistLink($f1, $a1, $level);

        // framework 2
        $f2 = $this->persistCompetency('f2');
        $c1 = $this->persistCompetency('c1', $f2);
        $a2 = $this->persistAbility('FOO B');
        $a3 = $this->persistAbility('BAR');
        $a4 = $this->persistAbility('FOO AB');
        $a5 = $this->persistAbility('FOO Z');
        $a6 = $this->persistAbility('FO');
        $a7 = $this->persistAbility('FOO D');
        $a8 = $this->persistAbility('FOO A');
        $this->persistLink($c1, $a2, $level);
        $this->persistLink($c1, $a3, $level);
        $this->persistLink($c1, $a4, $level);
        $this->persistLink($c1, $a5, $level);
        $this->persistLink($c1, $a6, $level);
        $this->persistLink($c1, $a7, $level);
        $this->persistLink($c1, $a8, $level);

        $this->om->flush();

        $this->assertEquals([], $this->repo->findFirstByName('AR', $f1));
        $this->assertEquals([$a3], $this->repo->findFirstByName('BA', $f1));
        $this->assertEquals([$a6, $a8, $a4, $a2, $a7], $this->repo->findFirstByName('FO', $f1));
        $this->assertEquals([$a8, $a4, $a2, $a7, $a5], $this->repo->findFirstByName('FOO', $f1));
        $this->assertEquals([$a8, $a4], $this->repo->findFirstByName('FOO A', $f1));
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
