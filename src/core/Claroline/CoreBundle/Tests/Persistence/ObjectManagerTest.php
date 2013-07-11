<?php

namespace Claroline\CoreBundle\Persistence;

use Mockery as m;
use Doctrine\ORM\Query;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class ObjectManagerTest extends MockeryTestCase
{
    /**
     * @dataProvider hasSupportMethodProvider
     */
    public function testHasSupportMethods($managerClass, $method, $returnValue)
    {
        $om = new ObjectManager(m::mock($managerClass));
        $this->assertEquals($returnValue, $om->{$method}());
    }

    /**
     * @dataProvider        wrappedManagerDependentMethodProvider
     * @expectedException   Claroline\CoreBundle\Persistence\UnsupportedMethodException
     */
    public function testWrappedManagerDependentMethodsThrowAnExceptionOnUnsupportedMethods($method)
    {
        $om = new ObjectManager(m::mock('Doctrine\Common\Persistence\ObjectManager'));
        $om->{$method}();
    }

    /**
     * @dataProvider transactionMethodProvider
     */
    public function testTransactionMethods($method)
    {
        $oom = m::mock('Doctrine\ORM\EntityManagerInterface');
        $cn = m::mock('Doctrine\DBAL\Connection');
        $oom->shouldReceive('getConnection')->once()->andReturn($cn);
        $cn->shouldReceive($method)->once();
        $om = new ObjectManager($oom);
        $om->{$method}();
    }

    public function testGetEventManager()
    {
        $oom = m::mock('Doctrine\ORM\EntityManagerInterface');
        $oom->shouldReceive('getEventManager')->once()->andReturn('evm');
        $om = new ObjectManager($oom);
        $this->assertEquals('evm', $om->getEventManager());
    }

    public function testGetUnitOfWork()
    {
        $oom = m::mock('Doctrine\ORM\EntityManagerInterface');
        $oom->shouldReceive('getUnitOfWork')->once()->andReturn('uow');
        $om = new ObjectManager($oom);
        $this->assertEquals('uow', $om->getUnitOfWork());
    }

    /**
     * @expectedException Claroline\CoreBundle\Persistence\NoFlushSuiteStartedException
     */
    public function testEndFlushSuiteThrowsAnExceptionIfNoSuiteHasBeenStarted()
    {
        $om = new ObjectManager(m::mock('Doctrine\Common\Persistence\ObjectManager'));
        $om->endFlushSuite();
    }

    public function testFlushCallsWrappedManagerFlushIfNoFlushSuiteIsActive()
    {
        $oom = m::mock('Doctrine\Common\Persistence\ObjectManager');
        $oom->shouldReceive('flush')->once();
        $om = new ObjectManager($oom);
        $om->flush();
    }

    public function testFlushHasNoEffectIfAFlushSuiteIsActive()
    {
        $oom = m::mock('Doctrine\Common\Persistence\ObjectManager');
        $oom->shouldReceive('flush')->never();
        $om = new ObjectManager($oom);
        $om->startFlushSuite();
        $om->flush();
    }

    public function testNestedFlushSuites()
    {
        $oom = m::mock('Doctrine\Common\Persistence\ObjectManager');
        $oom->shouldReceive('flush')->once();
        $om = new ObjectManager($oom);
        $om->startFlushSuite();
        $om->flush();
        $om->flush();
        $om->startFlushSuite();
        $om->flush();
        $om->flush();
        $om->endFlushSuite();
        $om->flush();
        $om->endFlushSuite();
    }

    public function testFactory()
    {
        $om = new ObjectManager(m::mock('Doctrine\Common\Persistence\ObjectManager'));
        $this->assertInstanceOf('stdClass', $om->factory('stdClass'));
    }

    /**
     * @expectedException Claroline\CoreBundle\Persistence\MissingObjectException
     */
    public function testFindByIdsThrowsAnExceptionIfSomeEntitiesCannotBeRetreived()
    {
        $oom = m::mock('Doctrine\ORM\EntityManager');
        $query = m::mock(new Query($oom)); // proxied partial mock (class is final)
        $oom->shouldReceive('createQuery')->once()->andReturn($query);
        $query->shouldReceive('getResult')->once()->andReturn(array('object 1'));
        $om = new ObjectManager($oom);
        $om->findByIds('Foo\Bar', array(1, 2));
    }

    public function testFindByIds()
    {
        $oom = m::mock('Doctrine\ORM\EntityManager');
        $query = m::mock(new Query($oom));
        $oom->shouldReceive('createQuery')
            ->once()
            ->with('SELECT object FROM Foo\Bar object WHERE object.id IN (:ids)')
            ->andReturn($query);
        $query->shouldReceive('setParameter')->with('ids', array(1, 2))->once();
        $query->shouldReceive('getResult')->once()->andReturn(array('object 1', 'object 2'));
        $om = new ObjectManager($oom);
        $this->assertEquals(array('object 1', 'object 2'), $om->findByIds('Foo\Bar', array(1, 2)));
    }

    public function testCount()
    {
        $oom = m::mock('Doctrine\ORM\EntityManager');
        $query = m::mock(new Query($oom));
        $oom->shouldReceive('createQuery')
            ->once()
            ->with('SELECT COUNT(object) FROM Foo\Bar object')
            ->andReturn($query);
        $query->shouldReceive('getSingleScalarResult')->once()->andReturn(5);
        $om = new ObjectManager($oom);
        $this->assertEquals(5, $om->count('Foo\Bar'));
    }

    public function hasSupportMethodProvider()
    {
        return array(
            array('Doctrine\Common\Persistence\ObjectManager', 'supportsTransactions', false),
            array('Doctrine\Common\Persistence\ObjectManager', 'hasEventManager', false),
            array('Doctrine\ORM\EntityManagerInterface', 'supportsTransactions', true),
            array('Doctrine\ORM\EntityManagerInterface', 'hasEventManager', true)
        );
    }

    public function wrappedManagerDependentMethodProvider()
    {
        return array(
            array('beginTransaction'),
            array('commit'),
            array('rollBack'),
            array('getEventManager'),
            array('getUnitOfWork')
        );
    }

    public function transactionMethodProvider()
    {
        return array(
            array('beginTransaction'),
            array('commit'),
            array('rollBack'),
        );
    }
}