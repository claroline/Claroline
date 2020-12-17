<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library;

/**
 * Base class of all the plugin bundles on the claroline platform.
 */
abstract class DistributionPluginBundle extends PluginBundle
{
    public function getVersion(): string
    {
        $data = file_get_contents(realpath($this->getPath().'/../../VERSION.txt'));
        $dataParts = explode("\n", $data);

        return trim($dataParts[0]);
    }
}
