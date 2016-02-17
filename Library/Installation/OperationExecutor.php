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

        $current = $this->openRepository($this->previousRepoFile);
        $target = $this->openRepository($this->installedRepoFile);
        $operations = [];

        foreach ($target as $targetName => $targetPackage) {
            if (!isset($current[$targetName])) {
                $this->log("  - Installation of {$targetName} required");
                $operation = $this->buildOperation(Operation::INSTALL, $targetPackage);
                $operations[$operation->getBundleFqcn()] = $operation;
            } elseif ($targetPackage->{'version-normalized'}
                !== $current[$targetName]->{'version-normalized'}) {
                $this->log("  - Update of {$targetName} required");
                $operation =$this->buildOperation(Operation::UPDATE, $targetPackage);
                $operation->setFromVersion($current[$targetName]->{'version-normalized'});
                $operation->setToVersion($targetPackage->{'version-normalized'});
                $operations[$operation->getBundleFqcn()] = $operation;
            }
        }

        $this->log("Sorting operations...");
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
     * process can be resumed after an error (e.g. an error) without triggering
     * again already executed operations. When there's no more operation to
     * execute, the snapshot of the previous local repository is deleted.
     *
     * @param Operation[] $operations
     * @throws ExecutorException if the the previous repository file is not writable
     */
    public function execute(array $operations)
    {
        $this->log("Executing install/update operations...");

        $previousRepo = $this->openRepository($this->previousRepoFile, false);

        if (!is_writable($this->previousRepoFile)) {
            throw new ExecutorException("'{$this->previousRepoFile}' must be writable");
        }

        $bundles = $this->getBundlesByFqcn();

        foreach ($operations as $operation) {
            $installer = $operation->getPackageType() === 'claroline-core' ?
                $this->baseInstaller :
                $this->pluginInstaller;

            if ($operation->getType() === Operation::INSTALL) {
                $installer->install($bundles[$operation->getBundleFqcn()]);
                $this->updatePreviousRepo($previousRepo, $operation->getRawPackage(), true);
            } elseif ($operation->getType() === Operation::UPDATE) {
                $installer->update(
                    $bundles[$operation->getBundleFqcn()],
                    $operation->getFromVersion(),
                    $operation->getToVersion()
                );
                $this->updatePreviousRepo($previousRepo, $operation->getRawPackage());
            }
        }

        $this->log("Removing previous local repository snapshot...");
        $filesystem = new Filesystem();
        $filesystem->remove($this->previousRepoFile);
    }

    private function openRepository($repoFile, $filter = true)
    {
        if (!file_exists($repoFile)) {
            throw new ExecutorException(
                "Repository file '{$repoFile}' doesn't exist",
                ExecutorException::REPO_NOT_FOUND
            );
        }

        $repo = json_decode(file_get_contents($repoFile));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ExecutorException(
                "Repository file '{$repoFile}' isn't valid JSON",
                ExecutorException::REPO_NOT_JSON
            );
        }

        if (!is_array($repo)) {
            throw new ExecutorException(
                "Repository file '{$repoFile}' doesn't contain an array of packages",
                ExecutorException::REPO_NOT_ARRAY
            );
        }

        $packages = !$filter ? $repo : array_filter($repo, function ($package) {
            return $package->type === 'claroline-core' || $package->type === 'claroline-plugin';
        });

        $packagesByName = [];

        foreach ($packages as $package) {
            $packagesByName[$package->name] = $package;
        }

        return $packagesByName;
    }

    private function buildOperation($type, \stdClass $package)
    {
        $vendorDir = $this->kernel->getRootDir() . '/../vendor';
        $targetDir = property_exists($package, 'targetDir') ? $package->targetDir : '';
        $packageDir = empty($targetDir) ? $package->name : "{$targetDir}/{$package->name}";
        $fqcn = $this->detector->detectBundle("{$vendorDir}/{$packageDir}");

        return new Operation($type, $package, $fqcn);
    }

    private function getBundlesByFqcn()
    {
        $byFqcn = array();

        foreach ($this->kernel->getBundles() as $bundle) {
            $byFqcn[get_class($bundle)] = $bundle;
        }

        return $byFqcn;
    }

    private function updatePreviousRepo(array $repo, \stdClass $package, $add = true)
    {
        if ($add) {
            $repo[] = $package;
        } else {
            foreach ($repo as $index => $previousPackage) {
                if ($previousPackage->name === $package->name) {
                    $repo[$index] = $package;
                }
            }
        }

        $options = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        file_put_contents($this->previousRepoFile, json_encode($repo, $options));
    }
}
