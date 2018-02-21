<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class StrictDispatcherTest extends MockeryTestCase
{
    /**
     * @expectedException \Claroline\AppBundle\Event\MissingEventClassException
     */
    public function testDispatchThrowsExceptionOnInvalidClass()
    {
        $dispatcher = $this->mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $claroDispatcher = new StrictDispatcher($dispatcher);
        $claroDispatcher->dispatch('noClass', 'FakeClass', []);
    }

    /**
     * @expectedException \Claroline\AppBundle\Event\MandatoryEventException
     */
    public function testDispatchThrowsExceptionOnMandatoryNotObserved()
    {
        $dispatcher = $this->mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $claroDispatcher = new StrictDispatcher($dispatcher);
        $dispatcher->shouldReceive('hasListeners')->once()->andReturn(false);
        $claroDispatcher->dispatch('notObserved', 'CreateFormResource', []);
    }

    /**
     * @expectedException \Claroline\AppBundle\Event\NotPopulatedEventException
     */
    public function testDispatchThrowsExceptionOnConveyorNotPopulated()
    {
        $dispatcher = $this->mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $claroDispatcher = new StrictDispatcher($dispatcher);
        $dispatcher->shouldReceive('hasListeners')->once()->andReturn(true);
        $dispatcher->shouldReceive('dispatch')->once();
        $claroDispatcher->dispatch('notPopulated', 'CreateFormResource', []);
    }

    public function testDispatch()
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            'test_populated',
            function (Event $event) {
                $event->setResponseContent('content');
            }
        );
        $claroDispatcher = new StrictDispatcher($dispatcher);
        $event = $claroDispatcher->dispatch('test_populated', 'CreateFormResource', []);
        $this->assertEquals('content', $event->getResponseContent());
    }
}
