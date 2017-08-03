<?php

namespace HeVinci\CompetencyBundle\Tests\Repository;

use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class CompetencyAbilityRepositoryTest extends RepositoryTestCase
{
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->repo = $this->om->getRepository('HeVinciCompetencyBundle:CompetencyAbility');
    }

    public function testCountByAbility()
    {
        $level = $this->persistLevel('l1', $this->persistScale('scale'));
        $c1 = $this->persistCompetency('c1');
        $c2 = $this->persistCompetency('c2');
        $a1 = $this->persistAbility('a1');
        $a2 = $this->persistAbility('a2');
        $this->persistLink($c1, $a1, $level);
        $this->persistLink($c1, $a2, $level);
        $this->persistLink($c2, $a1, $level);
        $this->om->flush();

        $this->assertEquals(2, $this->repo->countByAbility($a1));
    }

    public function testCountByCompetency()
    {
        $level = $this->persistLevel('l1', $this->persistScale('scale'));
        $c1 = $this->persistCompetency('c1');
        $c2 = $this->persistCompetency('c2');
        $a1 = $this->persistAbility('a1');
        $a2 = $this->persistAbility('a2');
        $a3 = $this->persistAbility('a3');
        $this->persistLink($c1, $a1, $level);
        $this->persistLink($c2, $a1, $level);
        $this->persistLink($c2, $a2, $level);
        $this->persistLink($c2, $a3, $level);
        $this->om->flush();

        $this->assertEquals(3, $this->repo->countByCompetency($c2));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFindOneByTermsExpectsAResult()
    {
        $c = $this->persistCompetency('c');
        $a = $this->persistAbility('a');
        $this->repo->findOneByTerms($c, $a);
    }

    public function testFindOneByTerms()
    {
        $level = $this->persistLevel('l', $this->persistScale('s'));
        $c = $this->persistCompetency('c');
        $a = $this->persistAbility('a');
        $link = $this->persistLink($c, $a, $level);
        $this->om->flush();

        $this->assertEquals($link, $this->repo->findOneByTerms($c, $a));
    }
}
