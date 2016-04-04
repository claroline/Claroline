<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class QueryStringWriterTest extends MockeryTestCase
{
    private $accessor;
    private $container;
    private $query;

    protected function setUp()
    {
        parent::setUp();
        $this->accessor = $this->mock('Claroline\CoreBundle\Library\Resource\ModeAccessor');
        $this->container = $this->mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->query = $this->mock('Symfony\Component\HttpFoundation\ParameterBag');
        $request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $request->query = $this->query;
        $this->container->shouldReceive('get')->with('request')->andReturn($request);
    }

    public function testWriterReturnsAnEmptyStringIfNotInRequestScope()
    {
        $this->container->shouldReceive('isScopeActive')->with('request')->andReturn(false);
        $writer = new QueryStringWriter($this->container, $this->accessor);
        $this->assertEquals('', $writer->getQueryString());
    }

    public function testWriterReturnsAnEmptyStringIfNoParametersWerePassedInTheRequest()
    {
        $this->container->shouldReceive('isScopeActive')->with('request')->andReturn(true);
        $this->accessor->shouldReceive('isPathMode')->andReturn(false);
        $this->query->shouldReceive('get')->withAnyArgs()->andReturnNull();
        $writer = new QueryStringWriter($this->container, $this->accessor);
        $this->assertEquals('', $writer->getQueryString());
    }

    public function testWriterIsAwareOfPathModeFlag()
    {
        $this->container->shouldReceive('isScopeActive')->with('request')->andReturn(true);
        $this->accessor->shouldReceive('isPathMode')->andReturn(true);
        $this->query->shouldReceive('get')->withAnyArgs()->andReturnNull();
        $writer = new QueryStringWriter($this->container, $this->accessor);
        $this->assertEquals('_mode=path', $writer->getQueryString());
    }

    public function testWriterRetainsWorkspaceAndBreadcrumbsParameters()
    {
        $this->container->shouldReceive('isScopeActive')->with('request')->andReturn(true);
        $this->accessor->shouldReceive('isPathMode')->andReturn(false);
        $this->query->shouldReceive('get')->with('_breadcrumbs')->andReturn(array('123', '456'));
        $writer = new QueryStringWriter($this->container, $this->accessor);
        $this->assertEquals(
            '_breadcrumbs%5B0%5D=123&_breadcrumbs%5B1%5D=456',
            $writer->getQueryString()
        );
    }
}
