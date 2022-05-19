<?php

namespace HeVinci\CompetencyBundle\Tests\Repository;

use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class ScaleRepositoryTest extends RepositoryTestCase
{
    private $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->om->getRepository(Scale::class);
    }

    public function testFindWithStatus()
    {
        $s1 = $this->persistScale('s1');
        $s2 = $this->persistScale('s2');
        $s3 = $this->persistScale('s3');

        $level = $this->persistLevel('l1', $s1);

        $c1 = $this->persistCompetency('c1', null, $s1);
        $c2 = $this->persistCompetency('c2', null, $s1);
        $this->persistCompetency('c3', null, $s2);

        $a1 = $this->persistAbility('a1');
        $a2 = $this->persistAbility('a2');
        $a3 = $this->persistAbility('a3');

        $this->persistLink($c1, $a1, $level);
        $this->persistLink($c1, $a2, $level);
        $this->persistLink($c2, $a3, $level);

        $this->om->flush();

        $expected = [
            ['id' => $s1->getId(), 'name' => 's1', 'competencies' => 2, 'abilities' => 3],
            ['id' => $s2->getId(), 'name' => 's2', 'competencies' => 1, 'abilities' => 0],
            ['id' => $s3->getId(), 'name' => 's3', 'competencies' => 0, 'abilities' => 0],
        ];

        $this->assertEquals($expected, $this->repo->findWithStatus());
    }

    public function testFindCompetencyCount()
    {
        $scale = $this->persistScale('s1');
        $c1 = $this->persistCompetency('c1', null, $scale);
        $this->persistCompetency('c2', $c1);
        $this->persistCompetency('c3', null, $scale);
        $this->persistCompetency('c4', null, $scale);
        $this->om->flush();
        $this->assertEquals(3, $this->repo->findCompetencyCount($scale));
    }

    public function testFindAbilityCount()
    {
        $scale = $this->persistScale('s1');
        $level = $this->persistLevel('l1', $scale);
        $c1 = $this->persistCompetency('c1', null, $scale);
        $c2 = $this->persistCompetency('c2', $c1);
        $a1 = $this->persistAbility('a1');
        $a2 = $this->persistAbility('a2');
        $a3 = $this->persistAbility('a3');
        $this->persistLink($c1, $a1, $level);
        $this->persistLink($c1, $a2, $level);
        $this->persistLink($c2, $a3, $level);
        $this->om->flush();
        $this->assertEquals(3, $this->repo->findAbilityCount($scale));
    }
}
