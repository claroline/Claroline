<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Tests\Persistence;

use Claroline\AppBundle\Persistence\NoFlushSuiteStartedException;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Persistence\UnsupportedMethodException;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

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
     * @dataProvider wrappedManagerDependentMethodProvider
     */
    public function testWrappedManagerDependentMethodsThrowAnExceptionOnUnsupportedMethods($method)
    {
        $this->expectException(UnsupportedMethodException::class);

        $om = new ObjectManager($this->mock('Doctrine\Persistence\ObjectManager'));
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

    public function testEndFlushSuiteThrowsAnExceptionIfNoSuiteHasBeenStarted()
    {
        $this->expectException(NoFlushSuiteStartedException::class);

        $om = new ObjectManager($this->mock('Doctrine\Persistence\ObjectManager'));
        $om->endFlushSuite();
    }

    public function testFlushCallsWrappedManagerFlushIfNoFlushSuiteIsActive()
    {
        $oom = $this->mock('Doctrine\Persistence\ObjectManager');
        $oom->shouldReceive('flush')->once();
        $om = new ObjectManager($oom);
        $om->flush();
    }

    public function testFlushHasNoEffectIfAFlushSuiteIsActive()
    {
        $oom = $this->mock('Doctrine\Persistence\ObjectManager');
        $oom->shouldReceive('flush')->never();
        $om = new ObjectManager($oom);
        $om->startFlushSuite();
        $om->flush();
    }

    public function testNestedFlushSuites()
    {
        $oom = $this->mock('Doctrine\Persistence\ObjectManager');
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

    public function hasSupportMethodProvider()
    {
        return [
            ['Doctrine\Persistence\ObjectManager', 'supportsTransactions', false],
            ['Doctrine\Persistence\ObjectManager', 'hasEventManager', false],
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
}
