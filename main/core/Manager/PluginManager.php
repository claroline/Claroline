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

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Plugin;
use Symfony\Component\HttpKernel\KernelInterface;
use Claroline\KernelBundle\Manager\BundleManager;
use Claroline\CoreBundle\Library\PluginBundle;

/**
 * @DI\Service("claroline.manager.plugin_manager")
 */
class PluginManager
{
    private $iniFileManager;
    private $kernelRootDir;
    private $om;
    private $pluginRepo;
    private $kernel;
    private $bundleManager;
    private $loadedBundles;
    private $installedBundles;

    /**
     * @DI\InjectParams({
     *      "iniFileManager" = @DI\Inject("claroline.manager.ini_file_manager"),
     *      "kernelRootDir"  = @DI\Inject("%kernel.root_dir%"),
     *      "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *      "kernel"         = @DI\Inject("kernel")
     * })
     */
    public function __construct(
        IniFileManager $iniFileManager,
        $kernelRootDir,
        ObjectManager $om,
        KernelInterface $kernel
    ) {
        $this->iniFileManager = $iniFileManager;
        $this->kernelRootDir = $kernelRootDir;
        $this->om = $om;
        $this->pluginRepo = $om->getRepository('ClarolineCoreBundle:Plugin');
        $this->iniFile = $this->kernelRootDir.'/config/bundles.ini';
        $this->kernel = $kernel;
        $this->loadedBundles = parse_ini_file($this->iniFile);
        BundleManager::initialize($kernel, $this->iniFile);
        $this->bundleManager = BundleManager::getInstance();
    }

    public function getDistributionVersion()
    {
        foreach ($this->bundleManager->getActiveBundles(true) as $bundle) {
            if ($bundle['instance']->getName() === 'ClarolineCoreBundle') {
                return $bundle['instance']->getVersion();
            }
        }
    }

    public function updateIniFile($vendor, $bundle)
    {
        $iniFile = $this->kernelRootDir.'/config/bundles.ini';

        //update ini file
        $this->iniFileManager
            ->updateKey(
                $vendor.'\\'.$bundle.'Bundle\\'.$vendor.$bundle.'Bundle',
                true,
                $iniFile
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

    public function getPlugins()
    {
        return $this->pluginRepo->findAll();
    }

    public function getPluginsData()
    {
        $plugins = $this->pluginRepo->findBy(array(), array('vendorName' => 'ASC', 'bundleName' => 'ASC'));
        $datas = [];

        foreach ($plugins as $plugin) {
            $datas[] = array(
                'id' => $plugin->getId(),
                'name' => $plugin->getVendorName().$plugin->getBundleName(),
                'vendor' => $plugin->getVendorName(),
                'bundle' => $plugin->getBundleName(),
                'has_options' => $plugin->hasOptions(),
                'description' => $this->getDescription($plugin),
                'is_loaded' => $this->isLoaded($plugin),
                'version' => $this->getVersion($plugin),
                'origin' => $this->getOrigin($plugin),
                'is_ready' => $this->isReady($plugin),
                'require' => $this->getRequirements($plugin),
                'required_by' => $this->getRequiredBy($plugin),
                'is_locked' => $this->isLocked($plugin),
            );
        }

        return $datas;
    }

    public function enable(Plugin $plugin)
    {
        $this->iniFileManager
            ->updateKey(
                $plugin->getBundleFQCN(),
                true,
                $this->kernelRootDir.'/config/bundles.ini'
            );

        //cache the results
        $this->loadedBundles = parse_ini_file($this->iniFile);

        return $plugin;
    }

    public function disable(Plugin $plugin)
    {
        $this->iniFileManager
            ->updateKey(
                $plugin->getBundleFQCN(),
                false,
                $this->kernelRootDir.'/config/bundles.ini'
            );

        //cache the results
        $this->loadedBundles = parse_ini_file($this->iniFile);

        return $plugin;
    }

    public function getEnabled($shortName = false)
    {
        $enabledBundles = [];

        foreach ($this->loadedBundles as $bundle => $enabled) {
            if ($enabled) {
                if ($shortName) {
                    $parts = explode('\\', $bundle);
                    $enabledBundles[] = $parts[2];
                } else {
                    $enabledBundles[] = $bundles;
                }
            }
        }

        //maybe only keep plugins that are in the database ? but it's one more request
        //we could also parse composer.json and so on...

        return $enabledBundles;
    }

    public function getPluginByShortName($name)
    {
        return $this->pluginRepo->findPluginByShortName($name);
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     */
    public function isLoaded($plugin)
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
        return $this->iniFile;
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     */
    public function getDescription($plugin)
    {
        return $this->getBundle($plugin)->getOrigin();
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     */
    public function getOrigin($plugin)
    {
        return $this->getBundle($plugin)->getOrigin();
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     */
    public function getVersion($plugin)
    {
        return $this->getBundle($plugin)->getVersion();
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     */
    public function getRequirements($plugin)
    {
        $requirements = [];
        $bundle = $this->getBundle($plugin);

        if (count($extensions = $bundle->getPhpExtensionRequirements()) > 0) {
            $requirements['extension'] = $extensions;
        }

        if (count($extensions = $bundle->getPluginsRequirements()) > 0) {
            $requirements['plugin'] = $extensions;
        }

        if (count($extra = $bundle->getExtraRequirements()) > 0) {
            foreach ($extra as $require) {
                $requirements['extra'][] = $require['failure_msg'];
            }
        }

        return $requirements;
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     */
    public function getMissingRequirements($plugin)
    {
        $requirements = $this->getRequirements($plugin);
        $bundle = $this->getBundle($plugin);
        $errors = [];

        if ($requirements) {
            if (array_key_exists('extension', $requirements)) {
                $errors['extension'] = $this->checkExtensionRequirements($requirements['extension']);
            }
            if (array_key_exists('plugin', $requirements)) {
                $errors['plugin'] = $this->checkPluginsRequirements($requirements['plugin']);
            }
            if (array_key_exists('extra', $requirements)) {
                $errors['extra'] = $this->checkExtraRequirements($bundle->getExtraRequirements());
            }
        }

        return $errors;
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     */
    public function isReady($plugin)
    {
        $errors = $this->getMissingRequirements($plugin);
        $errorCount = 0;

        if (array_key_exists('extension', $errors)) {
            $errorCount += count($errors['extension']);
        }
        if (array_key_exists('plugin', $errors)) {
            $errorCount += count($errors['plugin']);
        }

        if (array_key_exists('extra', $errors)) {
            $errorCount += count($errors['extra']);
        }

        return $errorCount === 0;
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     */
    public function getRequiredBy($plugin)
    {
        $requiredBy = [];
        $plugin = $this->getBundle($plugin);

        foreach ($this->getInstalledBundles() as $bundle) {
            $requirements = $bundle['instance']->getPluginsRequirements();
            if (in_array(get_class($plugin), $requirements)) {
                $requiredBy[] = get_class($bundle['instance']);
            }
        }

        return $requiredBy;
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
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
     */
    public function getBundle($plugin)
    {
        $shortName = $this->getPluginShortName($plugin);

        foreach ($this->getInstalledBundles() as $bundle) {
            if ($bundle['instance']->getName() === $shortName) {
                return $bundle['instance'];
            }
        }
    }

    public function getInstalledBundles()
    {
        return array_filter($this->bundleManager->getActiveBundles(true), function ($bundle) {
            return $bundle['instance'] instanceof PluginBundle;
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

    private function getPluginShortName($plugin)
    {
        $name = $plugin instanceof Plugin ?
            $plugin->getVendorName().$plugin->getBundleName() :
            $plugin;

        $parts = explode('\\', $name);

        return count($parts) === 3 ? $parts[2] : $name;
    }
}
