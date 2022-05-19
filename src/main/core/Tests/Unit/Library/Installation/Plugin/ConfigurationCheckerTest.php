<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Testing\StubPluginTrait;
use Symfony\Component\Yaml\Parser;

class ConfigurationCheckerTest extends MockeryTestCase
{
    use StubPluginTrait;

    private $checker;

    protected function setUp(): void
    {
        parent::setUp();

        $resourceTypeRepo = $this->mock('Claroline\CoreBundle\Repository\Resource\ResourceTypeRepository');
        $resourceTypeRepo->shouldReceive('findAll')->andReturn([]);
        $toolRepo = $this->mock('Claroline\CoreBundle\Repository\Resource\ResourceTypeRepository');
        $toolRepo->shouldReceive('findAll')->andReturn([]);
        $menuActionRepo = $this->mock('Claroline\CoreBundle\Repository\Resource\ResourceActionRepository');
        $menuActionRepo->shouldReceive('findBy')->with(['resourceType' => null, 'isCustom' => true])->andReturn([]);
        $widgetRepo = $this->mock('Claroline\CoreBundle\Repository\Widget\WidgetRepository');
        $widgetRepo->shouldReceive('findAll')->andReturn([]);
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $em->shouldReceive('getRepository')
            ->with(ResourceType::class)
            ->andReturn($resourceTypeRepo);
        $em->shouldReceive('getRepository')
            ->with(Tool::class)
            ->andReturn($toolRepo);
        $em->shouldReceive('getRepository')
            ->with(Widget::class)
            ->andReturn($widgetRepo);
        $em->shouldReceive('getRepository')
            ->with(MenuAction::class)
            ->andReturn($menuActionRepo);
        $this->checker = new ConfigurationChecker(new Parser(), $em);
    }

    public function testCheckerReturnsAnErrorOnNonExistentResourceFile()
    {
        $pluginFqcn = 'Invalid\NonExistentConfigFile1\InvalidNonExistentConfigFile1';
        $errors = $this->checker->check($this->loadPlugin($pluginFqcn));
        $this->assertStringContainsString('config.yml file missing', $errors[0]->getMessage());
    }

    public function testCheckerReturnsAnErrorOnMissingResourceKey()
    {
        $pluginFqcn = 'Invalid\MissingResourceKey1\InvalidMissingResourceKey1';
        $errors = $this->checker->check($this->loadPlugin($pluginFqcn));
        $this->assertTrue($errors[0] instanceof ValidationError);
    }

    public function testCheckerReturnsAnErrorOnUnloadableResourceClass()
    {
        $pluginFqcn = 'Invalid\UnloadableResourceClass1\InvalidUnloadableResourceClass1';
        $errors = $this->checker->check($this->loadPlugin($pluginFqcn));
        $this->assertTrue($errors[0] instanceof ValidationError);
        $this->assertStringContainsString('was not found', $errors[0]->getMessage());
    }

    public function testCheckerReturnsAnErrorOnUnloadableResourceClass2()
    {
        $pluginFqcn = 'Invalid\UnloadableResourceClass2\InvalidUnloadableResourceClass2';
        $this->requirePluginClass('Invalid\UnloadableResourceClass2\Entity\ResourceX');
        $errors = $this->checker->check($this->loadPlugin($pluginFqcn));
        $this->assertTrue($errors[0] instanceof ValidationError);
        $this->assertStringContainsString('must extend', $errors[0]->getMessage());
    }

    /**
     * @dataProvider provideValidPlugins
     */
    public function testCheckerReturnsNoErrorOnValidPlugin($pluginFqcn)
    {
        $this->assertEquals(0, count($this->checker->check($this->loadPlugin($pluginFqcn))));
    }
}
