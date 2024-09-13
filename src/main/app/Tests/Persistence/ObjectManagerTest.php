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
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class ObjectManagerTest extends MockeryTestCase
{
    public function testEndFlushSuiteThrowsAnExceptionIfNoSuiteHasBeenStarted()
    {
        $this->expectException(NoFlushSuiteStartedException::class);

        $om = new ObjectManager($this->mock('Doctrine\ORM\EntityManager'));
        $om->endFlushSuite();
    }

    public function testFlushCallsWrappedManagerFlushIfNoFlushSuiteIsActive()
    {
        $oom = $this->mock('Doctrine\ORM\EntityManager');
        $oom->shouldReceive('flush')->once();
        $om = new ObjectManager($oom);
        $om->flush();
    }

    public function testFlushHasNoEffectIfAFlushSuiteIsActive()
    {
        $oom = $this->mock('Doctrine\ORM\EntityManager');
        $oom->shouldReceive('flush')->never();
        $om = new ObjectManager($oom);
        $om->startFlushSuite();
        $om->flush();
    }

    public function testNestedFlushSuites()
    {
        $oom = $this->mock('Doctrine\ORM\EntityManager');
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
}
