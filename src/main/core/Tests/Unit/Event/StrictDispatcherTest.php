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
    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = $this->mock('Symfony\Component\EventDispatcher\EventDispatcher');
    }

    public function testDispatchThrowsExceptionOnInvalidClass()
    {
        $this->expectException(MissingEventClassException::class);

        $claroDispatcher = new StrictDispatcher(
            $this->dispatcher
        );
        $claroDispatcher->dispatch('noClass', 'FakeClass', []);
    }

    public function testDispatchThrowsExceptionOnMandatoryNotObserved()
    {
        $this->expectException(MandatoryEventException::class);

        $claroDispatcher = new StrictDispatcher(
            $this->dispatcher
        );
        $this->dispatcher->shouldReceive('hasListeners')->once()->andReturn(false);
        $this->dispatcher->shouldReceive('addSubscriber')->once();
        $claroDispatcher->dispatch('notObserved', 'Resource\ResourceAction', []);
    }

    public function testDispatchThrowsExceptionOnConveyorNotPopulated()
    {
        $this->expectException(NotPopulatedEventException::class);

        $claroDispatcher = new StrictDispatcher(
            $this->dispatcher
        );
        $this->dispatcher->shouldReceive('hasListeners')->once()->andReturn(true);
        $this->dispatcher->shouldReceive('addSubscriber')->once();
        $this->dispatcher->shouldReceive('dispatch')->once();
        $claroDispatcher->dispatch('notPopulated', 'Tool\ConfigureTool', ['toolName', 'context']);
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
        $claroDispatcher = new StrictDispatcher(
            $dispatcher
        );
        $this->dispatcher->shouldReceive('addSubscriber')->once();
        $this->dispatcher->shouldReceive('dispatch')->once();
        $event = $claroDispatcher->dispatch('test_populated', 'Tool\OpenTool', []);
        $this->assertEquals('content', $event->getData()[0]);
    }
}
