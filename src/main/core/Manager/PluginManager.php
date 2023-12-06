<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Parser\IniParser;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Repository\PluginRepository;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Claroline\KernelBundle\Recorder\BundleFileLoader;
use Symfony\Component\HttpKernel\KernelInterface;

class PluginManager
{
    private string $bundleFile;
    private PluginRepository $pluginRepo;
    private KernelInterface $kernel;
    private BundleFileLoader $bundleManager;

    public function __construct(
        string $bundleFile,
        ObjectManager $om,
        KernelInterface $kernel
    ) {
        $this->pluginRepo = $om->getRepository(Plugin::class);
        $this->bundleFile = $bundleFile;
        $this->kernel = $kernel;

        BundleFileLoader::initialize($kernel->getEnvironment(), $this->bundleFile);
        $this->bundleManager = BundleFileLoader::getInstance();
    }

    /**
     * Get the list of enabled plugins (aka. bundles with the PluginBundleInterface loaded in the kernel).
     */
    public function getEnabled(): array
    {
        $enabledBundles = [];
        foreach ($this->kernel->getBundles() as $bundle) {
            // It would be better to filter the bundles to only keep PluginBundleInterface
            // If I only keep real Claroline plugins, I loose ui injected by ClarolineAppBundle
            $enabledBundles[] = $bundle->getName();
        }

        return $enabledBundles;
    }

    public function getInstalledBundles(): array
    {
        return array_filter($this->bundleManager->getActiveBundles(true), function ($bundle) {
            return $bundle instanceof PluginBundleInterface;
        });
    }

    public function enable(Plugin $plugin): Plugin
    {
        IniParser::updateKey(
            $plugin->getBundleFQCN(),
            true,
            $this->bundleFile
        );

        return $plugin;
    }

    public function disable(Plugin $plugin): Plugin
    {
        IniParser::updateKey(
            $plugin->getBundleFQCN(),
            false,
            $this->bundleFile
        );

        return $plugin;
    }

    public function getPluginByShortName(string $name): ?Plugin
    {
        return $this->pluginRepo->findPluginByShortName($name);
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     */
    public function getMissingRequirements(Plugin $plugin): array
    {
        $requirements = $this->getRequirements($plugin);
        $bundle = $this->getBundle($plugin);

        return [
            'extensions' => $this->checkExtensionRequirements($requirements['extensions']),
            'plugins' => $this->checkPluginsRequirements($requirements['plugins']),
            'extras' => $this->checkExtraRequirements($bundle->getExtraRequirements()),
        ];
    }

    public function isReady(Plugin $plugin): bool
    {
        $errors = $this->getMissingRequirements($plugin);
        $errorCount = count($errors['extensions'])
            + count($errors['plugins'])
            + count($errors['extras']);

        return 0 === $errorCount;
    }

    public function isLoaded(string $pluginName): bool
    {
        $bundle = $this->getBundle($pluginName);

        try {
            $this->kernel->getBundle($bundle->getName());

            return true;
        } catch (\Exception $e) {
        }

        return false;
    }

    public function getVersion(Plugin $plugin): string
    {
        return $this->getBundle($plugin)->getVersion();
    }

    public function getRequirements(Plugin $plugin): array
    {
        $bundle = $this->getBundle($plugin);

        return [
            'extensions' => $bundle->getRequiredExtensions(),
            'plugins' => $bundle->getRequiredPlugins(),
            'extras' => array_map(function ($require) {
                return $require['failure_msg'];
            }, $bundle->getExtraRequirements()),
        ];
    }

    public function getRequiredBy(Plugin $plugin): array
    {
        $requiredBy = [];
        $plugin = $this->getBundle($plugin);

        foreach ($this->getInstalledBundles() as $bundle) {
            $requirements = $bundle->getRequiredPlugins();
            if (in_array(get_class($plugin), $requirements)) {
                $requiredBy[] = get_class($bundle);
            }
        }

        return $requiredBy;
    }

    public function isLocked(Plugin $plugin): bool
    {
        $requiredBy = $this->getRequiredBy($plugin);

        foreach ($requiredBy as $required) {
            if ($this->isLoaded($required)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     *
     * @deprecated
     */
    private function getBundle($plugin)
    {
        $shortName = $this->getPluginShortName($plugin);

        foreach ($this->getInstalledBundles() as $bundle) {
            if ($bundle->getName() === $shortName) {
                return $bundle;
            }
        }

        return null;
    }

    private function checkExtensionRequirements(array $extensions): array
    {
        $errors = [];

        foreach ($extensions as $extension) {
            if (!extension_loaded($extension)) {
                $errors[] = $extension;
            }
        }

        return $errors;
    }

    private function checkExtraRequirements(array $extra): array
    {
        $errors = [];

        foreach ($extra as $requirement) {
            // anonymous function
            $return = $requirement['test']();

            if (!$return) {
                $errors[] = $requirement['failure_msg'];
            }
        }

        return $errors;
    }

    private function checkPluginsRequirements(array $plugins): array
    {
        $errors = [];

        foreach ($plugins as $fqcn) {
            if (!$this->isLoaded($fqcn)) {
                $errors[] = $fqcn;
            }
        }

        return $errors;
    }

    private function getPluginShortName($plugin): string
    {
        $name = $plugin instanceof Plugin ?
            $plugin->getSfName() :
            $plugin;

        $parts = explode('\\', $name);

        return 3 === count($parts) ? $parts[2] : $name;
    }
}
