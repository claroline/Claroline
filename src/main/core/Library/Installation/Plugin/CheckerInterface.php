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

use Claroline\CoreBundle\Library\PluginBundleInterface;

/**
 * Interface of the plugin checkers. Plugin checkers are used by the validator
 * to determine if a given plugin could be safely installed by the installer.
 */
interface CheckerInterface
{
    /**
     * Performs the validation of a plugin.
     *
     * @param PluginBundleInterface $plugin
     * @param bool                  $updateMode
     *
     * @return ValidationError[]
     */
    public function check(PluginBundleInterface $plugin, $updateMode = false);
}
