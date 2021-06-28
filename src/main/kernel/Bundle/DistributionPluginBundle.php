<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\KernelBundle\Bundle;

/**
 * Base class of all the plugins embedded by the claroline platform (aka plugins defined in src/).
 */
abstract class DistributionPluginBundle extends PluginBundle
{
    final public function getVersion(): string
    {
        $data = file_get_contents(realpath($this->getPath().'/../../../VERSION.txt'));
        $dataParts = explode("\n", $data);

        return trim($dataParts[0]);
    }
}
