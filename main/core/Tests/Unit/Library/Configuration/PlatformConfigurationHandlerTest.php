<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Configuration;

use org\bovigo\vfs\vfsStream;

class PlatformConfigurationHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var PlatformConfigurationHandler */
    private $handler;

    /** @var string */
    private $configFile;

    protected function setUp()
    {
        vfsStream::setup('configDir', null, array('platform_options.yml' => ''));
        $this->configFile = vfsStream::url('configDir/platform_options.yml');
        $this->handler = new PlatformConfigurationHandler($this->configFile);
    }

    /**
     * @dataProvider parameterAccessorProvider
     */
    public function testHandlerThrowsAnExceptionOnNonExistentParameterAccessAttempt($accessor)
    {
        $this->setExpectedException('RuntimeException');
        $this->handler->{$accessor}('non_existent_parameter', 'some_value');
    }

    public function testExistentParameterCanBeAccessed()
    {
        $this->handler->setParameter('name', 'foo');
        $this->assertEquals('foo', $this->handler->getParameter('name'));
    }

    public function parameterAccessorProvider()
    {
        return array(
            array('getParameter'),
            array('setParameter'),
        );
    }
}
