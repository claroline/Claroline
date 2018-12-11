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
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Repository\PluginRepository;
use Claroline\KernelBundle\Manager\BundleManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @DI\Service("claroline.manager.plugin_manager")
 */
class PluginManager
{
    /** @var string */
    private $kernelRootDir;

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

    /**
     * PluginManager constructor.
     *
     * @DI\InjectParams({
     *      "kernelRootDir" = @DI\Inject("%kernel.root_dir%"),
     *      "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *      "kernel"        = @DI\Inject("kernel")
     * })
     *
     * @param string          $kernelRootDir
     * @param ObjectManager   $om
     * @param KernelInterface $kernel
     */
    public function __construct(
        $kernelRootDir,
        ObjectManager $om,
        KernelInterface $kernel
    ) {
        $this->kernelRootDir = $kernelRootDir;
        $this->om = $om;
        $this->pluginRepo = $om->getRepository('ClarolineCoreBundle:Plugin');
        $this->iniFile = $this->kernelRootDir.'/config/bundles.ini';
        $this->kernel = $kernel;

        $this->loadedBundles = IniParser::parseFile($this->iniFile);
        BundleManager::initialize($kernel, $this->iniFile);
        $this->bundleManager = BundleManager::getInstance();
    }

    public function updateIniFile($vendor, $bundle)
    {
        $iniFile = $this->kernelRootDir.'/config/bundles.ini';

        // update ini file
        IniParser::updateKey(
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

    public function getPluginsData()
    {
        /** @var Plugin[] $plugins */
        $plugins = $this->pluginRepo->findBy([], ['vendorName' => 'ASC', 'bundleName' => 'ASC']);
        $data = [];

        foreach ($plugins as $plugin) {
            $bundle = $this->getBundle($plugin);
            if ($bundle && !$bundle->isHidden()) {
                $data[] = [
                    'id' => $plugin->getId(),
                    'name' => $plugin->getShortName(),
                    'meta' => [
                        'version' => $this->getVersion($plugin),
                        'origin' => $this->getOrigin($plugin),
                        'vendor' => $plugin->getVendorName(),
                        'bundle' => $plugin->getBundleName(),
                    ],
                    'ready' => $this->isReady($plugin),
                    'enabled' => $this->isLoaded($plugin),
                    'locked' => $this->isLocked($plugin),

                    'hasOptions' => $plugin->hasOptions(),

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
            $this->kernelRootDir.'/config/bundles.ini'
        );

        //cache the results
        $this->loadedBundles = parse_ini_file($this->iniFile);

        return $plugin;
    }

    public function disable(Plugin $plugin)
    {
        IniParser::updateKey(
            $plugin->getBundleFQCN(),
            false,
            $this->kernelRootDir.'/config/bundles.ini'
        );

        //cache the results
        $this->loadedBundles = IniParser::parseFile($this->iniFile);

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
                    $enabledBundles[] = $bundle;
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
     *
     * @return bool
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
     *
     * @return bool
     */
    public function isHidden($plugin)
    {
        return $this->getBundle($plugin)->isHidden();
    }

    /**
     * @param mixed $plugin Plugin Entity, ShortName (ClarolineCoreBundle) Fqcn (Claroline\CoreBundle\ClarolineCoreBundle)
     *
     * @return string|null
     */
    public function getOrigin($plugin)
    {
        return $this->getBundle($plugin)->getOrigin();
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
     * @return bool
     */
    public function isActivatedByDefault($plugin)
    {
        $bundle = $this->getBundle($plugin);

        return $bundle->isActiveByDefault();
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
            $requirements = $bundle['instance']->getRequiredPlugins();
            if (in_array(get_class($plugin), $requirements)) {
                $requiredBy[] = get_class($bundle['instance']);
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
            if ($bundle['instance']->getName() === $shortName) {
                return $bundle['instance'];
            }
        }

        return null;
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

    public function getPluginShortName($plugin)
    {
        $name = $plugin instanceof Plugin ?
            $plugin->getVendorName().$plugin->getBundleName() :
            $plugin;

        $parts = explode('\\', $name);

        return 3 === count($parts) ? $parts[2] : $name;
    }
}
