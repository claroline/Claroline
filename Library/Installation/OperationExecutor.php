<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation;

use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Claroline\InstallationBundle\Manager\InstallationManager;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledFilesystemRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @DI\Service("claroline.installation.operation_executor")
 *
 * Installs/updates platform bundles based on the comparison of
 * previous and current local composer repositories (i.e. the file
 * "vendor/composer/installed.json" and its backup in "app/config").
 */
class OperationExecutor
{
    use LoggableTrait;

    private $kernel;
    private $baseInstaller;
    private $pluginInstaller;
    private $installedRepoFile;
    private $previousRepoFile;
    private $detector;

    /**
     * @DI\InjectParams({
     *     "kernel"             = @DI\Inject("kernel"),
     *     "baseInstaller"      = @DI\Inject("claroline.installation.manager"),
     *     "pluginInstaller"    = @DI\Inject("claroline.plugin.installer")
     * })
     */
    public function __construct(
        KernelInterface $kernel,
        InstallationManager $baseInstaller,
        Installer $pluginInstaller
    )
    {
        $this->kernel = $kernel;
        $this->baseInstaller = $baseInstaller;
        $this->pluginInstaller = $pluginInstaller;
        $this->previousRepoFile = $this->kernel->getRootDir() . '/config/previous-installed.json';
        $this->installedRepoFile = $this->kernel->getRootDir() . '/../vendor/composer/installed.json';
        $this->detector = new Detector();
    }

    /**
     * Overrides default local repository files (test purposes).
     *
     * @param string $previousRepoFile
     * @param string $installedRepoFile
     */
    public function setRepositoryFiles($previousRepoFile, $installedRepoFile)
    {
        $this->previousRepoFile = $previousRepoFile;
        $this->installedRepoFile = $installedRepoFile;
    }

    /**
     * Overrides the default bundle detector (test purposes).
     *
     * @param Detector $detector
     */
    public function setBundleDetector(Detector $detector)
    {
        $this->detector = $detector;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->baseInstaller->setLogger($logger);
        $this->pluginInstaller->setLogger($logger);
    }

    /**
     * Builds the list of operations to be executed based on the comparison
     * of previous and current installed dependencies.
     *
     * @return array
     */
    public function buildOperationList()
    {
        $this->log('Building install/update operations list...');

        $previous = $this->openRepository($this->previousRepoFile);
        $current = $this->openRepository($this->installedRepoFile);
        $operations = [];

        /** @var PackageInterface $currentPackage */
        foreach ($current->getCanonicalPackages() as $currentPackage) {
            if (!($previousPackage = $previous->findPackage($currentPackage->getName(), '*'))) {
                $this->log("Installation of {$currentPackage->getName()} required");
                $operation = $this->buildOperation(Operation::INSTALL, $currentPackage);
                $operations[$operation->getBundleFqcn()] = $operation;
            } elseif ($currentPackage->getVersion() !== $previousPackage->getVersion()
                || $currentPackage->isDev()) {
                $this->log(sprintf(
                    'Update of %s from %s to %s required',
                    $previousPackage->getName(),
                    $previousPackage->getVersion(),
                    $currentPackage->getVersion()
                ));
                $operation = $this->buildOperation(Operation::UPDATE, $currentPackage);
                $operation->setFromVersion($previousPackage->getVersion());
                $operation->setToVersion($currentPackage->getVersion());
                $operations[$operation->getBundleFqcn()] = $operation;
            }
        }

        // TODO: we *should* do something in case a platform package is
        // removed (e.g. if the package is a plugin, at least unregister it)
        // but AFAIK we don't have anything now to support removal of a bundle
        // whose sources are already gone. Maybe the platform installer could
        // look after each update if there are records in the plugin table
        // that don't match any known bundle?

        $this->log('Sorting operations...');
        $bundles = $this->kernel->getBundles();
        $sortedOperations = [];

        foreach ($bundles as $bundle) {
            $bundleClass = $bundle->getNamespace() ?
                $bundle->getNamespace() . '\\' . $bundle->getName() :
                $bundle->getName();

            if (isset($operations[$bundleClass])) {
                $sortedOperations[] = $operations[$bundleClass];
            }
        }

        return $sortedOperations;
    }

    /**
     * Executes a list of install/update operations. Each successful operation
     * is followed by an update of the previous local repository, so that the
     * process can be resumed after an interruption (e.g. due to an error)
     * without triggering again already executed operations. When there's no
     * more operation to execute, the snapshot of the previous local repository
     * is deleted.
     *
     * @param Operation[] $operations
     * @throws \RuntimeException if the the previous repository file is not writable
     */
    public function execute(array $operations)
    {
        $this->log('Executing install/update operations...');

        $previousRepo = $this->openRepository($this->previousRepoFile, false);

        if (!is_writable($this->previousRepoFile)) {
            throw new \RuntimeException(
                "'{$this->previousRepoFile}' must be writable",
                456 // this code is there for unit testing only
            );
        }

        $bundles = $this->getBundlesByFqcn();

        foreach ($operations as $operation) {
            $installer = $operation->getPackage()->getType() === 'claroline-core' ?
                $this->baseInstaller :
                $this->pluginInstaller;

            if ($operation->getType() === Operation::INSTALL) {
                $installer->install($bundles[$operation->getBundleFqcn()]);
                $previousRepo->addPackage(clone $operation->getPackage());
            } elseif ($operation->getType() === Operation::UPDATE) {
                $installer->update(
                    $bundles[$operation->getBundleFqcn()],
                    $operation->getFromVersion(),
                    $operation->getToVersion()
                );
                // there's no cleaner way to update the version of a package...
                $version = new \ReflectionProperty('Composer\Package\Package', 'version');
                $version->setAccessible(true);
                $version->setValue($operation->getPackage(), $operation->getToVersion());
            }

            $previousRepo->write();
        }

        $this->log('Removing previous local repository snapshot...');
        $filesystem = new Filesystem();
        $filesystem->remove($this->previousRepoFile);
    }

    /**
     * @param string    $repoFile
     * @param bool      $filter
     * @return InstalledFilesystemRepository
     */
    private function openRepository($repoFile, $filter = true)
    {
        $json = new JsonFile($repoFile);

        if (!$json->exists()) {
            throw new \RuntimeException(
                "Repository file '{$repoFile}' doesn't exist",
                123 // this code is there for unit testing only
            );
        }

        $repo = new InstalledFilesystemRepository($json);

        if ($filter) {
            foreach ($repo->getPackages() as $package) {
                if ($package->getType() !== 'claroline-core'
                    && $package->getType() !== 'claroline-plugin') {
                    $repo->removePackage($package);
                }
            }
        }

        return $repo;
    }

    private function buildOperation($type, PackageInterface $package)
    {
        $vendorDir = $this->kernel->getRootDir() . '/../vendor';
        $targetDir = $package->getTargetDir() ?: '';
        $packageDir = empty($targetDir) ?
            $package->getPrettyName() :
            "{$package->getName()}/{$targetDir}";
        $fqcn = $this->detector->detectBundle("{$vendorDir}/{$packageDir}");

        return new Operation($type, $package, $fqcn);
    }

    private function getBundlesByFqcn()
    {
        $byFqcn = array();

        foreach ($this->kernel->getBundles() as $bundle) {
            $fqcn = $bundle->getNamespace() ?
                $bundle->getNamespace() . '\\' . $bundle->getName() :
                $bundle->getName();
            $byFqcn[$fqcn] = $bundle;
        }

        return $byFqcn;
    }
}
