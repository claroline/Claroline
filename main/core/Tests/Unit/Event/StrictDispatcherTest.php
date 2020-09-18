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

use Claroline\AppBundle\Event\MandatoryEventException;
use Claroline\AppBundle\Event\MissingEventClassException;
use Claroline\AppBundle\Event\NotPopulatedEventException;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

class StrictDispatcherTest extends MockeryTestCase
{
    public function testDispatchThrowsExceptionOnInvalidClass()
    {
        $this->expectException(MissingEventClassException::class);

        $dispatcher = $this->mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $claroDispatcher = new StrictDispatcher($dispatcher);
        $claroDispatcher->dispatch('noClass', 'FakeClass', []);
    }

    public function testDispatchThrowsExceptionOnMandatoryNotObserved()
    {
        $this->expectException(MandatoryEventException::class);

        $dispatcher = $this->mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $claroDispatcher = new StrictDispatcher($dispatcher);
        $dispatcher->shouldReceive('hasListeners')->once()->andReturn(false);
        $claroDispatcher->dispatch('notObserved', 'Resource\ResourceAction', []);
    }

    public function testDispatchThrowsExceptionOnConveyorNotPopulated()
    {
        $this->expectException(NotPopulatedEventException::class);

        $dispatcher = $this->mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $claroDispatcher = new StrictDispatcher($dispatcher);
        $dispatcher->shouldReceive('hasListeners')->once()->andReturn(true);
        $dispatcher->shouldReceive('dispatch')->once();
        $claroDispatcher->dispatch('notPopulated', 'Tool\OpenTool', []);
    }

    public function testDispatch()
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            'test_populated',
            function (Event $event) {
                $event->setData(['content']);
            }
        );
        $claroDispatcher = new StrictDispatcher($dispatcher);
        $event = $claroDispatcher->dispatch('test_populated', 'Tool\OpenTool', []);
        $this->assertEquals('content', $event->getData()[0]);
    }
}
