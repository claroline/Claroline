<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BundleRecorder;

use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;
use Claroline\BundleRecorder\Handler\OperationHandler;
use Claroline\BundleRecorder\Operation;
use Composer\DependencyResolver\DefaultPolicy;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\DependencyResolver\Solver;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Repository\ArrayRepository;
use Composer\Repository\InstalledFilesystemRepository;
use Composer\Repository\PlatformRepository;

class Recorder
{
    private $detector;
    private $bundleHandler;
    private $operationHandler;
    private $vendorDir;
    private $removableBundles = array();
    private $logger;
    private $fromRepo;

    public function __construct(
        Detector $detector,
        BundleHandler $bundleHandler,
        OperationHandler $operationHandler,
        $vendorDir
    )
    {
        $this->detector = $detector;
        $this->bundleHandler = $bundleHandler;
        $this->operationHandler = $operationHandler;
        $this->vendorDir = $vendorDir;
        $installedFile = new JsonFile($this->vendorDir . '/composer/installed.json');
        $this->fromRepo = new InstalledFilesystemRepository($installedFile);
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    public function checkForPendingOperations()
    {
        if (!$this->operationHandler->isFileEmpty()) {
            throw new \Exception('A non empty operation file is already present (assumed not executed).');
        }
    }

    public function addInstallOperation(PackageInterface $package)
    {
        if ($this->isClarolinePackage($package)) {
            $bundle = $this->detector->detectBundle($package->getPrettyName());
            $type = $this->getOperationBundleType($package);
            $operation = new Operation(Operation::INSTALL, $bundle, $type);
            $this->operationHandler->addOperation($operation);
        }
    }

    public function addUpdateOperation(PackageInterface $target, PackageInterface $initial)
    {
        if ($this->isClarolinePackage($target)) {
            $bundle = $this->detector->detectBundle($target->getPrettyName());
            $type = $this->getOperationBundleType($target);
            $operation = new Operation(Operation::UPDATE, $bundle, $type);
            $operation->setFromVersion($initial->getVersion());
            $operation->setToVersion($target->getVersion());
            $this->operationHandler->addOperation($operation);
        }
    }

    public function addRemovablePackage(PackageInterface $package)
    {
        if ($this->isClarolinePackage($package)) {
            $bundleFqcn = $this->detector->detectBundle($package->getPrettyName());
            $this->removableBundles[$package->getPrettyName()] = $bundleFqcn;
        }
    }

    public function addUninstallOperation(PackageInterface $package)
    {
        if (isset($this->removableBundles[$package->getPrettyName()])) {
            $bundleFqcn = $this->removableBundles[$package->getPrettyName()];
            $type = $this->getOperationBundleType($package);
            $operation = new Operation(Operation::UNINSTALL, $bundleFqcn, $type);
            $this->operationHandler->addOperation($operation);
            unset($this->removableBundles[$package->getPrettyName()]);
        }
    }

    public function buildBundleFile()
    {
        $orderedBundles = array();

        foreach ($this->fromRepo->getCanonicalPackages() as $package) {
            $prettyName = $package->getPrettyName();
            $bundles = $this->detector->detectBundles($prettyName);
            $autoload = $package->getAutoload();

            if (count($bundles) > 0) {
                if (isset($autoload['psr-4'])) {
                    foreach ($bundles as $index => $bundle) {
                        // psr-4 prefix must be prepended to bundle class
                        $prefixes = array_keys($autoload['psr-4']);
                        $bundles[$index] = array_shift($prefixes) . $bundle;
                    }
                }

                $orderedBundles = array_merge($orderedBundles, $bundles);
            }
        }

        $orderedBundles = $this->orderClaroBundlesForInstall($orderedBundles);
        $this->bundleHandler->writeBundleFile(array_unique($orderedBundles));
    }

    /**
     * Returns if the $element is a claroline package.
     *
     * @param PackageInterface|string $element
     * @return boolean
     */
    private function isClarolinePackage($element)
    {
        if ($element instanceof PackageInterface) {
            return $element->getType() === 'claroline-core' || $element->getType() === 'claroline-plugin';
        }

        foreach ($this->fromRepo->getCanonicalPackages() as $package) {
            if ($element === $package->getPrettyName()) {
                return $this->isClarolinePackage($package);
            }
        }
    }

    private function getOperationBundleType(PackageInterface $package)
    {
        return $package->getType() === 'claroline-core' ?
            Operation::BUNDLE_CORE :
            Operation::BUNDLE_PLUGIN;
    }

    /**
     * This is a simplistic sort but it's enough for now 
     * (it won't handle recursive/multiple dependencies but it's enough for now)
     */
    public function orderClaroBundlesForInstall(array $bundles)
    {
        //maybe use $this->fromRepo instead
        $installedBundles = json_decode(file_get_contents($this->vendorDir . '/composer/installed.json'));
        $claroBundles = array();

        foreach ($installedBundles as $installedBundle) {
            if ($installedBundle->type === 'claroline-core'
                || $installedBundle->type === 'claroline-plugin') {
                $claroBundles[$this->getNameSpace($installedBundle->name)] =
                    $this->getDependencies($installedBundle->require);
            }
        }

        $bundleCopies = $bundles;

        foreach ($claroBundles as $claroBundle => $dependencies) {
            foreach ($dependencies as $dependency) {
                if (!$this->isDependencySetBefore($bundles, $claroBundle, $dependency)) {
                    //we have to place the bundle properly
                    $key = array_search($claroBundle, $bundleCopies);
                    $newKey = $this->arrayRightShift($dependency, $bundleCopies);
                    $bundleCopies[$newKey] = $claroBundle;
                    unset($bundleCopies[$key]);
                    $bundleCopies = array_values($bundleCopies);
                }
            }
        }

        return $bundleCopies;
    }

    /**
     * Shift an array to the right.
     * Example: $array = array([0] => 'a', [1] => 'b', [2] => 'c');
     * $return = arrayRightShift('a', $array);
     * $array = array([0] => 'a', [1] => 'b', [2] => 'b', [3] => c)
     */
    private function arrayRightShift($search, &$array) {
        $key = array_search($search, $array);

        for ($i = count($array); $i > $key; $i--) {
            $array[$i] = $array[$i - 1];
        }

        return $key + 1;
    }

    private function isDependencySetBefore(array $bundles, $bundle, $dependency) {
        $foundDep = false;

        foreach ($bundles as $initBundle) {
            if ($bundle === $dependency) $foundDep = true;
            if ($bundle === $initBundle) {
                return $foundDep;
            }
        }
    }

    /**
     * Returns the dependencies of a Claroline package.
     *
     * @param The require class wich was parsed from the installed.json with json_decode
     * @return array
     */
    private function getDependencies(\StdClass $require)
    {
        $dependencies = [];

        foreach($require as $name => $el) {
            if ($this->isClarolinePackage($name)) {
                $dependencies[] = $this->getNameSpace($name);
            }
        }

        return $dependencies;
    }

    private function getNameSpace($prettyName)
    {
        foreach ($this->fromRepo->getCanonicalPackages() as $package) {
            if ($prettyName === $package->getPrettyName()) {
                $autoload = $package->getAutoload();
                if (isset($autoload['psr-0'])) {
                    $key = array_keys($autoload['psr-0']);
                    $prefixe = array_shift($key);

                    return $prefixe . '\\' . str_replace('\\', '', $prefixe);
                } else {
                    return $prettyName;
                }
            }
        }
    }
}
