<?php

namespace Claroline\CoreBundle\Event;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

class DispatcherTest extends MockeryTestCase
{

    /**
     * @group dispatcher
     */
    public function testDispatchThrowsExceptionOnMandatoryNotObserved()
    {
        $dispatcher = m::mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $claroDispatcher = new Dispatcher($dispatcher);
        $this->setExpectedException('Claroline\CoreBundle\Event\Event\MandatoryEventException');
        $dispatcher->shouldReceive('hasListeners')->once()->andReturn(false);
        $claroDispatcher->dispatch('notObserved', 'CreateFormResource', array());
    }

    /**
     * @group dispatcher
     */
    public function testDispatchThrowsExceptionOnConvyeorNotPopulated()
    {
        $dispatcher = m::mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $claroDispatcher = new Dispatcher($dispatcher);
        $this->setExpectedException('Claroline\CoreBundle\Event\PopulateEventException');
        $dispatcher->shouldReceive('hasListeners')->once()->andReturn(true);
        $dispatcher->shouldReceive('dispatch')->once();
        $claroDispatcher->dispatch('notPopulated', 'CreateFormResource', array());
    }

    /**
     * @group dispatcher
     */
    public function testDispatcherDispatchs()
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('test_populated', function(Event $event){
            $event->setResponseContent('content');
        });

        $claroDispatcher = new Dispatcher($dispatcher);
        $event = $claroDispatcher->dispatch('test_populated', 'CreateFormResource', array());
        $this->assertEquals('content', $event->getResponseContent());
    }

}