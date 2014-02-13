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
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    public function install(PackageInterface $package)
    {
        $installableBundles = array();

        if ($package->getType() === 'claroline-core' || $package->getType() === 'claroline-plugin') {
            $bundle = $this->detector->detectBundle($package->getPrettyName());
            $type = $package->getType() === 'claroline-core' ?
                Operation::BUNDLE_CORE :
                Operation::BUNDLE_PLUGIN;
            $operation = new Operation(Operation::INSTALL, $bundle, $type);
            $this->operationHandler->addOperation($operation);
            $installableBundles[] = $bundle;
        } else {
            $installableBundles = $this->detector->detectBundles($package->getPrettyName());
        }

        $this->bundleHandler->addBundles($installableBundles);
    }

    public function update(PackageInterface $target, PackageInterface $initial)
    {
        if ($target->getType() === 'claroline-core' || $target->getType() === 'claroline-plugin') {
            $bundle = $this->detector->detectBundle($target->getPrettyName());
            $type = $target->getType() === 'claroline-core' ?
                Operation::BUNDLE_CORE :
                Operation::BUNDLE_PLUGIN;
            $operation = new Operation(Operation::UPDATE, $bundle, $type);
            $operation->setFromVersion($initial->getVersion());
            $operation->setToVersion($target->getVersion());
            $this->operationHandler->addOperation($operation);
        }
    }

    public function addRemovablePackage(PackageInterface $package)
    {
        $bundles = $this->detector->detectBundles($package->getPrettyName());

        foreach ($bundles as $bundleFqcn) {
            $this->removableBundles[$package->getPrettyName()][] = $bundleFqcn;
        }
    }

    public function uninstall(PackageInterface $package)
    {
        if (isset($this->removableBundles[$package->getPrettyName()])) {
            if (is_dir($this->vendorDir . '/' . $package->getPrettyName())) {
                return;
            }

            if ($package->getType() === 'claroline-core' || $package->getType() === 'claroline-plugin') {
                foreach ($this->removableBundles[$package->getPrettyName()] as $bundleFqcn) {
                    $type = $package->getType() === 'claroline-core' ?
                        Operation::BUNDLE_CORE :
                        Operation::BUNDLE_PLUGIN;
                    $operation = new Operation(Operation::UNINSTALL, $bundleFqcn, $type);
                    $this->operationHandler->addOperation($operation);
                }
            }

            $this->bundleHandler->removeBundles($this->removableBundles[$package->getPrettyName()]);
            unset($this->removableBundles[$package->getPrettyName()]);
        }
    }

    public function updateBundlesOrder()
    {
        $installedFile = new JsonFile($this->vendorDir . '/composer/installed.json');
        $fromRepo = new InstalledFilesystemRepository($installedFile);
        $toRepo = new ArrayRepository();

        $pool = new Pool();
        $pool->addRepository($fromRepo);
        $pool->addRepository(new PlatformRepository());
        $request = new Request($pool);

        foreach ($fromRepo->getPackages() as $package) {
            $request->install($package->getName());
        }

        $solver = new Solver(new DefaultPolicy(), $pool, $toRepo);
        $operations = $solver->solve($request);
        $orderedBundles = array();

        foreach ($operations as $operation) {
            $bundles = $this->detector->detectBundles($operation->getPackage()->getPrettyName());

            if (count($bundles) > 0) {
                $orderedBundles = array_merge($orderedBundles, $bundles);
            }
        }

        $this->bundleHandler->reorderBundles(array_unique($orderedBundles));
    }

    public function checkForPreviousOperations()
    {
        if (!$this->operationHandler->isFileEmpty()) {
            throw new \Exception('A non empty operation file is already present (assumed not executed).');
        }
    }
}
