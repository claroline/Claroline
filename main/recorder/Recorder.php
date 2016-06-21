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
use Composer\DependencyResolver\DefaultPolicy;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\DependencyResolver\Solver;
use Composer\IO\NullIO;
use Composer\Package\Package;
use Composer\Repository\ArrayRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RepositoryInterface;

class Recorder
{
    private $localRepo;
    private $detector;
    private $bundleHandler;
    private $vendorDir;
    private $aliases;

    public function __construct(
        RepositoryInterface $localRepo,
        Detector $detector,
        BundleHandler $bundleHandler,
        array $aliases,
        $vendorDir
    ) {
        $this->localRepo = $localRepo;
        $this->detector = $detector;
        $this->bundleHandler = $bundleHandler;
        $this->aliases = $aliases;
        $this->vendorDir = $vendorDir;
    }

    public function buildBundleFile()
    {
        $operations = $this->getOperations();
        $orderedBundles = [];

        foreach ($operations as $operation) {
            $package = $operation->getPackage();
            $prettyName = $package->getPrettyName();
            $bundles = $this->detector->detectBundles($prettyName);

            if (count($bundles) > 0) {
                $orderedBundles = array_merge($orderedBundles, $bundles);
            }
        }

        $this->bundleHandler->writeBundleFile(array_unique($orderedBundles));
    }

    public function removeBupIniFile()
    {
        $this->bundleHandler->removeBupIniFile();
    }

    /**
     * @return InstallOperation[]
     */
    private function getOperations()
    {
        foreach ($this->aliases as $alias) {
            // we need to replace the version of aliased packages in the local
            // repository by their aliases in the root package (composer always
            // stores the actual installed version instead), otherwise the whole
            // dependency resolution below will fail.
            $aliased = $this->localRepo->findPackage($alias['package'], $alias['version']);
            $version = new \ReflectionProperty('Composer\Package\Package', 'version');
            $version->setAccessible(true);
            $version->setValue($aliased, $alias['alias_normalized']);
        }

        $toRepo = new ArrayRepository();
        $pool = new Pool();
        $pool->addRepository($this->localRepo);
        $pool->addRepository(new PlatformRepository());
        $request = new Request($pool);

        foreach ($this->localRepo->getCanonicalPackages() as $package) {
            $request->install($package->getName());
        }

        $solver = new Solver(new DefaultPolicy(), $pool, $toRepo, new NullIO());

        return $solver->solve($request);
    }
}
