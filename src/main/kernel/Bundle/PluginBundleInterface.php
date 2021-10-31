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

use Claroline\InstallationBundle\Bundle\InstallableInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Interface of all the plugin bundles on the claroline platform.
 */
interface PluginBundleInterface extends BundleInterface, AutoConfigurableInterface, InstallableInterface
{
    public function getConfigFile();

    public function getRequiredExtensions();

    public function getRequiredPlugins();

    public function getExtraRequirements();

    public function getDescription();
}
