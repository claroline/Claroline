<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\PluginBundle;

/**
 * Interface of the plugin checkers. Plugin checkers are used by the validator
 * to determine if a given plugin could be safely installed by the installer.
 */
interface CheckerInterface
{
    /**
     * Performs the validation of a plugin.
     *
     * @throws Exception if the plugin is not valid
     *
     * @return null|array[ValidationError]
     */
    function check(PluginBundle $plugin);
}