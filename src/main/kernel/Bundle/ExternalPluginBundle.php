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
 * Base class of all the external plugins (aka plugins defined in vendor/).
 */
abstract class ExternalPluginBundle extends PluginBundle
{
    /**
     * For now, external plugins must follow the base Claroline platform versioning.
     */
    final public function getVersion(): string
    {
        if (file_exists(realpath($this->getPath().'/../../../VERSION.txt'))) {
            // path : vendor/VENDOR_NAME/BUNDLE_NAME
            $data = file_get_contents(realpath($this->getPath().'/../../../VERSION.txt'));
        } else {
            // meta package path : vendor/VENDOR_NAME/BUNDLE_NAME/(main|plugin|integration)/PLUGIN_NAME
            $data = file_get_contents(realpath($this->getPath().'/../../../../../VERSION.txt'));
        }

        $dataParts = explode("\n", $data);

        return trim($dataParts[0]);
    }
}
