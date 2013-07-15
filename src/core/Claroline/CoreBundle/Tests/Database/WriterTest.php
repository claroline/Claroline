<?php

namespace Claroline\CoreBundle\Database;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class WriterTest extends MockeryTestCase
{
    private $em;
    private $writer;

    protected function setUp()
    {
        parent::setUp();
        $this->em = m::mock('Doctrine\ORM\EntityManager');
        $this->writer = new Writer($this->em);
    }

    /**
     * @dataProvider writeMethodProvider
     */
    public function testWriteMethodsWithFlushEnabled($writerMethod, $emMethod)
    {
        $entity = new \stdClass();
        $this->em->shouldReceive($emMethod)->once()->with($entity);
        $this->em->shouldReceive('flush')->once();
        $this->writer->$writerMethod($entity);
    }

    /**
     * @dataProvider writeMethodProvider
     */
    public function testWriteMethodsWithFlushSuspended($writerMethod, $emMethod)
    {
        $entity = new \stdClass();
        $this->em->shouldReceive($emMethod)->once()->with($entity);
        $this->em->shouldReceive('flush')->times(0);
        $this->writer->suspendFlush();
        $this->writer->$writerMethod($entity);
    }

    public function testForceFlush()
    {
        $this->em->shouldReceive('flush')->once();
        $this->writer->forceFlush();
    }

    public function writeMethodProvider()
    {
        return array(
            array('create', 'persist'),
            array('update', 'persist'),
            array('delete', 'remove')
        );
    }
}