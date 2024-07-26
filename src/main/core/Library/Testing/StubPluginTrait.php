<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\Library\Installation\Plugin\Loader;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;

/**
 * Trait containing utility methods for test cases using stub plugins
 * (i.e. fake plugins living in Tests/Stub/plugin).
 */
trait StubPluginTrait
{
    /**
     * PHPUnit provider.
     */
    public function provideValidPlugins(): array
    {
        $this->requirePluginClass('Valid\WithCustomResources\Entity\ResourceA');
        $this->requirePluginClass('Valid\WithCustomResources\Entity\ResourceB');
        $this->requirePluginClass('Valid\WithCustomActions\Entity\ResourceX');

        return [
            ['Valid\Minimal\ValidMinimal'],
            ['Valid\Simple\ValidSimple'],
            ['Valid\Custom\ValidCustom'],
            ['Valid\WithMigrations\ValidWithMigrations'],
            ['Valid\WithCustomResources\ValidWithCustomResources'],
        ];
    }

    private function getPluginClassPath(string $classFqcn): string
    {
        return __DIR__
            .'/../../Tests/Stub/plugin/'
            .str_replace('\\', '/', $classFqcn)
            .'.php';
    }

    private function loadPlugin(string $pluginFqcn): PluginBundleInterface
    {
        return (new Loader())->load(
            $pluginFqcn,
            $this->getPluginClassPath($pluginFqcn)
        );
    }

    private function requirePluginClass(string $classFqcn): void
    {
        require_once $this->getPluginClassPath($classFqcn);
    }
}
