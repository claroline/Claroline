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
        vfsStream::setup('configDir', null, ['platform_options.yml' => '']);
        vfsStream::setup('configDir', null, ['locked_params.yml' => '']);
        $this->configFile = vfsStream::url('configDir/platform_options.yml');
        $this->lockedParams = vfsStream::url('configDir/locked_params.yml');
        $this->handler = new PlatformConfigurationHandler($this->configFile, $this->lockedParams);
    }

    public function testExistentParameterCanBeAccessed()
    {
        $this->handler->setParameter('name', 'foo');
        $this->assertEquals('foo', $this->handler->getParameter('name'));
    }

    public function parameterAccessorProvider()
    {
        return [
            ['getParameter'],
            ['setParameter'],
        ];
    }
}
