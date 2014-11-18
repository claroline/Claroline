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
    private $operations;

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
            $operation->setDependencies($this->getDependencies($package));
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
            $operation->setDependencies($this->getDependencies($target));
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
        $operations = $this->getOperations();

        foreach ($operations as $operation) {
            $package = $operation->getPackage();
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

        //The next line not be necessary. Some problems were caused by some recursive dependencies...
        //we may comment this line. However, since it's working properly now, I'm not touching anything
        //because it doesn't do anything wrong.
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

        $operations = $this->getOperations();

        foreach ($operations as $operation) {
            $package = $operation->getPackage();
            var_dump("comparing {$package->getPrettyName()} and {$element}");
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
        $claroBundles = array();
        $operations = $this->getOperations();

        foreach ($operations as $operation) {
            $package = $operation->getPackage();
            if ($package->getType() === 'claroline-core'
                || $package->getType() === 'claroline-plugin') {
                $claroBundles[$this->getNameSpace($package->getName())] =
                    $this->getDependencies($package);
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
    public function getDependencies(PackageInterface $package)
    {
        echo ("Searching dependencies for {$package}...");
        $dependencies = [];
        $requires = $package->getRequires();

        if ($requires) {
            foreach($requires as $name => $el) {
                var_dump($name);
                if ($this->isClarolinePackage($name)) {
                    $dependencies[] = $this->getNameSpace($name);
                }
            }
        }
        
        var_dump($dependencies);
        
        return $dependencies;
    }

    public function getOperations()
    {
        $toRepo = new ArrayRepository();
        $pool = new Pool();
        $pool->addRepository($this->fromRepo);
        $pool->addRepository(new PlatformRepository());
        $request = new Request($pool);

        foreach ($this->fromRepo->getPackages() as $package) {
            $request->install($package->getName());
        }

        $solver = new Solver(new DefaultPolicy(), $pool, $toRepo);
        $operations = $solver->solve($request);

        return $operations;
    }

    private function getNameSpace($prettyName)
    {
        $operations = $this->getOperations();

        foreach ($operations as $operation) {
            $package = $operation->getPackage();

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
