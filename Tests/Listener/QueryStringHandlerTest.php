<?php

namespace Claroline\CoreBundle\Listener;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class QueryStringHandlerTest extends MockeryTestCase
{
    private $accessor;
    private $writer;
    private $handler;

    protected function setUp()
    {
        parent::setUp();
        $this->writer = $this->mock('Claroline\CoreBundle\Library\Resource\QueryStringWriter');
        $this->accessor = $this->mock('Claroline\CoreBundle\Library\Resource\ModeAccessor');

        $this->handler = new QueryStringHandler($this->accessor, $this->writer);
    }

    public function testHandlerSetsThePathModeFlagToTrueIfNeeded()
    {
        $request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $event = $this->mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');

        $event->shouldReceive('getRequest')->andReturn($request);
        $request->shouldReceive('get')->with('_mode')->andReturn('path');
        $this->accessor->shouldReceive('setPathMode')->with(true);

        $this->handler->onKernelRequest($event);
    }

    public function testHandlerReappendsQueryStringParametersToRedirectUrl()
    {
        $response = $this->mock('Symfony\Component\HttpFoundation\RedirectResponse');
        $event = $this->mock('Symfony\Component\HttpKernel\Event\FilterResponseEvent');

        $event->shouldReceive('getResponse')->andReturn($response);
        $response->shouldReceive('getTargetUrl')->andReturn('foo/url');
        $this->writer->shouldReceive('getQueryString')->andReturn('a=1&b=2');
        $response->shouldReceive('setTargetUrl')->with('foo/url?a=1&b=2');

        $this->handler->onKernelResponse($event);
    }
}
