<?php

namespace Claroline\CoreBundle\Database;

use \Mockery as m;
use Doctrine\ORM\Query;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class GenericRepositoryTest extends MockeryTestCase
{
    private $query;
    private $em;
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->em = m::mock('Doctrine\ORM\EntityManager');
        $this->query = m::mock(new Query($this->em)); // proxied partial mock (class is final)
        $this->repo = new GenericRepository($this->em);
    }

    public function testFindByIds()
    {
        $this->em->shouldReceive('createQuery')
            ->once()
            ->with('SELECT entity FROM :entityClass entity WHERE entity.id IN (:ids)')
            ->andReturn($this->query);
        $this->query->shouldReceive('setParameter')->with('entityClass', 'Entity\Foo')->once();
        $this->query->shouldReceive('setParameter')->with('ids', array(1, 2))->once();
        $this->query->shouldReceive('getResult')->once()->andReturn(array('entity 1', 'entity 2'));
        $entities = $this->repo->findByIds('Entity\Foo', array(1, 2));
        $this->assertEquals(array('entity 1', 'entity 2'), $entities);
    }

    public function testFindByIdsThrowsAnExceptionIfSomeEntitiesCannotBeRetreived()
    {
        $this->setExpectedException('Claroline\CoreBundle\Database\MissingEntityException');
        $this->em->shouldReceive('createQuery')->once()->andReturn($this->query);
        $this->query->shouldReceive('getResult')->once()->andReturn(array('entity 1'));
        $this->repo->findByIds('Entity\Foo', array(1, 2));
    }
}