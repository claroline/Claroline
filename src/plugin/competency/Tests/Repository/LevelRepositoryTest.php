<?php

namespace HeVinci\CompetencyBundle\Tests\Repository;

use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class LevelRepositoryTest extends RepositoryTestCase
{
    private $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->om->getRepository(Level::class);
    }

    public function testGetFindByCompetencyBuilder()
    {
        $s1 = $this->persistScale('s1');
        $l1 = $this->persistLevel('l1', $s1);
        $l2 = $this->persistLevel('l2', $s1);
        $c1 = $this->persistCompetency('c1', null, $s1);
        $c2 = $this->persistCompetency('c2', $c1);

        // non relevant data
        $this->persistLevel('l3', $this->persistScale('s2'));

        $this->om->flush();

        $this->assertEquals(
            [$l1, $l2],
            $this->repo->getFindByCompetencyBuilder($c2)->getQuery()->getResult()
        );
    }
}
