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

/**
 * Trait containing utility methods for test cases using stub plugins
 * (i.e. fake plugins living in Tests/Stub/plugin).
 */
trait StubPluginTrait
{
    /**
     * PHPUnit provider.
     *
     * @return array
     */
    public function provideValidPlugins()
    {
        $this->requirePluginClass('Valid\WithCustomResources\Entity\ResourceA');
        $this->requirePluginClass('Valid\WithCustomResources\Entity\ResourceB');
        $this->requirePluginClass('Valid\WithCustomActions\Entity\ResourceX');

        return array(
            array('Valid\Minimal\ValidMinimal'),
            array('Valid\Simple\ValidSimple'),
            array('Valid\Custom\ValidCustom'),
            array('Valid\WithMigrations\ValidWithMigrations'),
            array('Valid\WithCustomResources\ValidWithCustomResources'),
        );
    }

    private function getPluginClassPath($classFqcn)
    {
        return __DIR__
            .'/../../Tests/Stub/plugin/'
            .str_replace('\\', '/', $classFqcn)
            .'.php';
    }

    private function loadPlugin($pluginFqcn)
    {
        return (new Loader())->load(
            $pluginFqcn,
            $this->getPluginClassPath($pluginFqcn)
        );
    }

    private function requirePluginClass($classFqcn)
    {
        require_once $this->getPluginClassPath($classFqcn);
    }
}
