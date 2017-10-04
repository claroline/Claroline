<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Persistence;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\ORM\Query;

class ObjectManagerTest extends MockeryTestCase
{
    /**
     * @dataProvider hasSupportMethodProvider
     */
    public function testHasSupportMethods($managerClass, $method, $returnValue)
    {
        $om = new ObjectManager($this->mock($managerClass));
        $this->assertEquals($returnValue, $om->{$method}());
    }

    /**
     * @dataProvider        wrappedManagerDependentMethodProvider
     * @expectedException   \Claroline\CoreBundle\Persistence\UnsupportedMethodException
     */
    public function testWrappedManagerDependentMethodsThrowAnExceptionOnUnsupportedMethods($method)
    {
        $om = new ObjectManager($this->mock('Doctrine\Common\Persistence\ObjectManager'));
        $om->{$method}();
    }

    /**
     * @dataProvider transactionMethodProvider
     */
    public function testTransactionMethods($method)
    {
        $oom = $this->mock('Doctrine\ORM\EntityManagerInterface');
        $cn = $this->mock('Doctrine\DBAL\Connection');
        $oom->shouldReceive('getConnection')->once()->andReturn($cn);
        $cn->shouldReceive($method)->once();
        $om = new ObjectManager($oom);
        $om->{$method}();
    }

    public function testGetEventManager()
    {
        $oom = $this->mock('Doctrine\ORM\EntityManagerInterface');
        $oom->shouldReceive('getEventManager')->once()->andReturn('evm');
        $om = new ObjectManager($oom);
        $this->assertEquals('evm', $om->getEventManager());
    }

    public function testGetUnitOfWork()
    {
        $oom = $this->mock('Doctrine\ORM\EntityManagerInterface');
        $oom->shouldReceive('getUnitOfWork')->once()->andReturn('uow');
        $om = new ObjectManager($oom);
        $this->assertEquals('uow', $om->getUnitOfWork());
    }

    /**
     * @expectedException \Claroline\CoreBundle\Persistence\NoFlushSuiteStartedException
     */
    public function testEndFlushSuiteThrowsAnExceptionIfNoSuiteHasBeenStarted()
    {
        $om = new ObjectManager($this->mock('Doctrine\Common\Persistence\ObjectManager'));
        $om->endFlushSuite();
    }

    public function testFlushCallsWrappedManagerFlushIfNoFlushSuiteIsActive()
    {
        $oom = $this->mock('Doctrine\Common\Persistence\ObjectManager');
        $oom->shouldReceive('flush')->once();
        $om = new ObjectManager($oom);
        $om->flush();
    }

    public function testFlushHasNoEffectIfAFlushSuiteIsActive()
    {
        $oom = $this->mock('Doctrine\Common\Persistence\ObjectManager');
        $oom->shouldReceive('flush')->never();
        $om = new ObjectManager($oom);
        $om->startFlushSuite();
        $om->flush();
    }

    public function testNestedFlushSuites()
    {
        $oom = $this->mock('Doctrine\Common\Persistence\ObjectManager');
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
        $om = new ObjectManager($this->mock('Doctrine\Common\Persistence\ObjectManager'));
        $this->assertInstanceOf('stdClass', $om->factory('stdClass'));
    }

    /**
     * @expectedException \Claroline\CoreBundle\Persistence\MissingObjectException
     */
    public function testFindByIdsThrowsAnExceptionIfSomeEntitiesCannotBeRetreived()
    {
        $oom = $this->mock('Doctrine\ORM\EntityManager');
        $query = $this->getQuery();
        $oom->shouldReceive('createQuery')->once()->andReturn($query);
        $query->shouldReceive('getResult')->once()->andReturn(['object 1']);
        $om = new ObjectManager($oom);
        $om->findByIds('Foo\Bar', [1, 2]);
    }

    public function testFindByIds()
    {
        $oom = $this->mock('Doctrine\ORM\EntityManager');
        $query = $this->getQuery();
        $oom->shouldReceive('createQuery')
            ->once()
            ->with('SELECT object FROM Foo\Bar object WHERE object.id IN (:list)')
            ->andReturn($query);
        $query->shouldReceive('setParameter')->with('list', [1, 2])->once();
        $query->shouldReceive('getResult')->once()->andReturn(['object 1', 'object 2']);
        $om = new ObjectManager($oom);
        $this->assertEquals(['object 1', 'object 2'], $om->findByIds('Foo\Bar', [1, 2]));
    }

    public function testCount()
    {
        $oom = $this->mock('Doctrine\ORM\EntityManager');
        $query = $this->getQuery();
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
        return [
            ['Doctrine\Common\Persistence\ObjectManager', 'supportsTransactions', false],
            ['Doctrine\Common\Persistence\ObjectManager', 'hasEventManager', false],
            ['Doctrine\ORM\EntityManagerInterface', 'supportsTransactions', true],
            ['Doctrine\ORM\EntityManagerInterface', 'hasEventManager', true],
        ];
    }

    public function wrappedManagerDependentMethodProvider()
    {
        return [
            ['beginTransaction'],
            ['commit'],
            ['rollBack'],
            ['getEventManager'],
            ['getUnitOfWork'],
        ];
    }

    public function transactionMethodProvider()
    {
        return [
            ['beginTransaction'],
            ['commit'],
            ['rollBack'],
        ];
    }

    private function getQuery()
    {
        $oom = $this->mock('Doctrine\ORM\EntityManager');
        $config = $this->mock('Doctrine\ORM\Configuration');
        $config->shouldReceive('getDefaultQueryHints')->andReturn('[]');
        $config->shouldReceive('isSecondLevelCacheEnabled')->andReturn(false);
        $oom->shouldReceive('getConfiguration')->andReturn($config);
        $query = $this->mock(new Query($oom));

        return $query;
    }
}
