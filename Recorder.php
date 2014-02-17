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
            $this->removableBundles[$package->getPrettyName()][] = $bundleFqcn;
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

        $this->bundleHandler->writeBundleFile(array_unique($orderedBundles));
    }

    private function isClarolinePackage(PackageInterface $package)
    {
        return $package->getType() === 'claroline-core' || $package->getType() === 'claroline-plugin';
    }

    private function getOperationBundleType(PackageInterface $package)
    {
        return $package->getType() === 'claroline-core' ?
            Operation::BUNDLE_CORE :
            Operation::BUNDLE_PLUGIN;
    }
}
