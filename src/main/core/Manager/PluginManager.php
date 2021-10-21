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
use Claroline\KernelBundle\Bundle\PluginBundle;
use Claroline\KernelBundle\Manager\BundleManager;
use Symfony\Component\HttpKernel\KernelInterface;

class PluginManager
{
    /** @var string */
    private $kernelRootDir;
    /** @var string */
    private $bundleFile;

    /** @var ObjectManager */
    private $om;

    /** @var PluginRepository */
    private $pluginRepo;

    /** @var KernelInterface */
    private $kernel;

    /** @var BundleManager */
    private $bundleManager;

    /** @var array */
    private $loadedBundles;

    public function __construct(
        string $kernelRootDir,
        string $bundleFile,
        ObjectManager $om,
        KernelInterface $kernel
    ) {
        $this->kernelRootDir = $kernelRootDir;
        $this->om = $om;
        $this->pluginRepo = $om->getRepository('ClarolineCoreBundle:Plugin');
        $this->bundleFile = $bundleFile;
        $this->kernel = $kernel;

        $this->loadedBundles = IniParser::parseFile($this->bundleFile);
        BundleManager::initialize($kernel->getEnvironment(), $this->bundleFile);
        $this->bundleManager = BundleManager::getInstance();
    }

    public function updateIniFile($vendor, $bundle)
    {
        // update ini file
        IniParser::updateKey(
            $vendor.'\\'.$bundle.'Bundle\\'.$vendor.$bundle.'Bundle',
            true,
            $this->bundleFile
        );
    }

    public function updateAutoload($ivendor, $ibundle, $vname, $bname)
    {
        //update namespace file
        $namespaces = $this->kernelRootDir.'/../vendor/composer/autoload_namespaces.php';
        $content = file_get_contents($namespaces);
        $lineToAdd = "\n    '{$ivendor}\\\\{$ibundle}Bundle' => array(\$vendorDir . '/{$vname}/{$bname}'),";

        if (!strpos($content, $lineToAdd)) {
            //add the correct line after corebundle...
            $content = str_replace(
                "/core-bundle'),",
                "/core-bundle'), {$lineToAdd}",
                $content
            );

            file_put_contents($namespaces, $content);
        }
    }

    public function getPluginsData()
    {
        /** @var Plugin[] $plugins */
        $plugins = $this->pluginRepo->findBy([], ['vendorName' => 'ASC', 'bundleName' => 'ASC']);
        $data = [];

        foreach ($plugins as $plugin) {
            $bundle = $this->getBundle($plugin);
            if ($bundle) {
                $data[] = [
                    'id' => $plugin->getId(),
                    'name' => $plugin->getShortName(),
                    'meta' => [
                        'version' => $this->getVersion($plugin),
                        'vendor' => $plugin->getVendorName(),
                        'bundle' => $plugin->getBundleName(),
                    ],
                    'ready' => $this->isReady($plugin),
                    'enabled' => $this->isLoaded($plugin),
                    'locked' => $this->isLocked($plugin),

                    'requirements' => $this->getRequirements($plugin),
                    'requiredBy' => $this->getRequiredBy($plugin),
                ];
            }
        }

        return $data;
    }

    public function enable(Plugin $plugin)
    {
        IniParser::updateKey(
            $plugin->getBundleFQCN(),
            true,
            $this->bundleFile
        );

        //cache the results
        $this->loadedBundles = parse_ini_file($this->bundleFile);

        return $plugin;
    }

    public function disable(Plugin $plugin)
    {
        IniParser::updateKey(
            $plugin->getBundleFQCN(),
            false,
            $this->bundleFile
        );

        //cache the results
        $this->loadedBundles = IniParser::parseFile($this->bundleFile);

        return $plugin;
    }

    public function getEnabled($shortName = false)
    {
        // retrieve all bundles registered in app
        $enabledBundles = [];
        foreach ($this->loadedBundles as $bundle => $enabled) {
            if ($enabled) {
                if ($shortName) {
                    $parts = explode('\\', $bundle);
                    $enabledBundles[] = $parts[2];
                } else {
                    $enabledBundles[] = $bundle;
                }
            }
        }

        // maybe keep only real claroline plugins

        return $enabledBundles;
    }

    public function getPluginByShortName($name)
    {
        return $this->pluginRepo->findPluginByShortName($name);
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     */
    public function isLoaded($plugin): bool
    {
        $pluginClass = get_class($this->getBundle($plugin));

        foreach ($this->loadedBundles as $bundle => $isEnabled) {
            if ($bundle === $pluginClass && $isEnabled) {
                return true;
            }
        }

        return false;
    }

    public function getIniFile()
    {
        return $this->bundleFile;
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     *
     * @return string
     */
    public function getVersion($plugin)
    {
        return $this->getBundle($plugin)->getVersion();
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     *
     * @return array
     */
    public function getRequirements($plugin)
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

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     *
     * @return array
     */
    public function getMissingRequirements($plugin)
    {
        $requirements = $this->getRequirements($plugin);
        $bundle = $this->getBundle($plugin);

        return [
            'extensions' => $this->checkExtensionRequirements($requirements['extensions']),
            'plugins' => $this->checkPluginsRequirements($requirements['plugins']),
            'extras' => $this->checkExtraRequirements($bundle->getExtraRequirements()),
        ];
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     *
     * @return bool
     */
    public function isReady($plugin)
    {
        $errors = $this->getMissingRequirements($plugin);
        $errorCount = count($errors['extensions'])
            + count($errors['plugins'])
            + count($errors['extras']);

        return 0 === $errorCount;
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     *
     * @return array
     */
    public function getRequiredBy($plugin)
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

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     *
     * @return bool
     */
    public function isLocked($plugin)
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
     * @return mixed
     */
    public function getBundle($plugin)
    {
        $shortName = $this->getPluginShortName($plugin);

        foreach ($this->getInstalledBundles() as $bundle) {
            if ($bundle->getName() === $shortName) {
                return $bundle;
            }
        }

        return null;
    }

    public function getInstalledBundles()
    {
        return array_filter($this->bundleManager->getActiveBundles(true), function ($bundle) {
            return $bundle instanceof PluginBundle;
        });
    }

    private function checkExtensionRequirements(array $extensions)
    {
        $errors = [];

        foreach ($extensions as $extension) {
            if (!extension_loaded($extension)) {
                $errors[] = $extension;
            }
        }

        return $errors;
    }

    public function checkExtraRequirements(array $extra)
    {
        $errors = [];

        foreach ($extra as $requirement) {
            //anonymous function
            $return = $requirement['test']();

            if (!$return) {
                $errors[] = $requirement['failure_msg'];
            }
        }

        return $errors;
    }

    private function checkPluginsRequirements(array $plugins)
    {
        $errors = [];

        foreach ($plugins as $fqcn) {
            if (!(array_key_exists($fqcn, $this->loadedBundles) && $this->loadedBundles[$fqcn])) {
                $errors[] = $fqcn;
            }
        }

        return $errors;
    }

    public function getPluginShortName($plugin)
    {
        $name = $plugin instanceof Plugin ?
            $plugin->getVendorName().$plugin->getBundleName() :
            $plugin;

        $parts = explode('\\', $name);

        return 3 === count($parts) ? $parts[2] : $name;
    }
}
