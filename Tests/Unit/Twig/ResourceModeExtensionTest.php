<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class ResourceModeExtensionTest extends MockeryTestCase
{
    private $generator;
    private $extension;

    protected function setUp()
    {
        parent::setUp();
        $writer = $this->mock('Claroline\CoreBundle\Library\Resource\QueryStringWriter');
        $accessor = $this->mock('Claroline\CoreBundle\Library\Resource\ModeAccessor');
        $this->generator = $this->mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');

        $writer->shouldReceive('getQueryString')->andReturnValues(array('', 'a=1&b=2'));

        $this->extension = new ResourceModeExtension($this->generator, $writer, $accessor);
    }

    public function testPathFunction()
    {
        $this->generator->shouldReceive('generate')->with('foo', array(), false)->andReturn('foo/path');
        $this->assertEquals('foo/path', $this->extension->getPath('foo'));
        $this->assertEquals('foo/path?a=1&b=2', $this->extension->getPath('foo'));
    }

    public function testUrlFunction()
    {
        $this->generator->shouldReceive('generate')->with('foo', array(), true)->andReturn('http://foo/path');
        $this->assertEquals('http://foo/path', $this->extension->getUrl('foo'));
        $this->assertEquals('http://foo/path?a=1&b=2', $this->extension->getUrl('foo'));
    }
}
