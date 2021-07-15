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

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Claroline\InstallationBundle\Manager\InstallationManager;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Installs/updates platform bundles based on the bundles config ini file.
 */
class OperationExecutor implements LoggerAwareInterface
{
    use LoggableTrait;

    private $kernel;
    private $baseInstaller;
    private $pluginInstaller;
    private $om;

    public function __construct(
        KernelInterface $kernel,
        InstallationManager $baseInstaller,
        Installer $pluginInstaller,
        ObjectManager $om
    ) {
        $this->kernel = $kernel;
        $this->baseInstaller = $baseInstaller;
        $this->pluginInstaller = $pluginInstaller;
        $this->om = $om;
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->baseInstaller->setLogger($logger);
        $this->pluginInstaller->setLogger($logger);
    }

    public function buildOperationListForBundles(array $bundles, ?string $fromVersion = null, ?string $toVersion = null)
    {
        $isFreshInstall = !$fromVersion && !$toVersion;
        $operations = [];
        foreach ($bundles as $bundle) {
            $bundleFqcn = get_class($bundle);
            // If plugin is installed, update it. Otherwise, install it.
            if (!$isFreshInstall && $this->isBundleAlreadyInstalled($bundleFqcn, false)) {
                $operations[$bundleFqcn] = new Operation(Operation::UPDATE, $bundle, $bundleFqcn);
                $operations[$bundleFqcn]->setFromVersion($fromVersion);
                $operations[$bundleFqcn]->setToVersion($toVersion);
            } else {
                $operations[$bundleFqcn] = new Operation(Operation::INSTALL, $bundle, $bundleFqcn);
            }
        }

        return $operations;
    }

    /**
     * Executes a list of install/update operations. Each successful operation
     * is followed by an update of the previous local repository, so that the
     * process can be resumed after an interruption (e.g. due to an error)
     * without triggering again already executed operations.
     *
     * @param Operation[] $operations
     */
    public function execute(array $operations)
    {
        $this->log('Executing install/update operations...');
        $bundles = $this->getBundlesByFqcn();

        foreach ($operations as $operation) {
            if (Operation::INSTALL === $operation->getType()) {
                $this->pluginInstaller->install($bundles[$operation->getBundleFqcn()]);
            } elseif (Operation::UPDATE === $operation->getType()) {
                if (array_key_exists($operation->getBundleFqcn(), $bundles)) {
                    $this->pluginInstaller->update(
                      $bundles[$operation->getBundleFqcn()],
                      $operation->getFromVersion(),
                      $operation->getToVersion()
                  );
                } else {
                    $this->log("Could not update {$operation->getBundleFqcn()}... Please update manually.", LogLevel::ERROR);
                }
            }
        }

        $this->end($operations);
    }

    public function end(array $operations)
    {
        $this->log('Ending operations...');
        $bundles = $this->getBundlesByFqcn();

        foreach ($operations as $operation) {
            if (Operation::INSTALL === $operation->getType()) {
                $this->pluginInstaller->end($bundles[$operation->getBundleFqcn()]);
            } elseif (Operation::UPDATE === $operation->getType()) {
                $this->pluginInstaller->end(
                    $bundles[$operation->getBundleFqcn()],
                    $operation->getFromVersion(),
                    $operation->getToVersion()
                );
            }
        }
    }

    private function getBundlesByFqcn()
    {
        $byFqcn = [];
        foreach ($this->kernel->getBundles() as $bundle) {
            $fqcn = $bundle->getNamespace() ?
                $bundle->getNamespace().'\\'.$bundle->getName() :
                $bundle->getName();
            $byFqcn[$fqcn] = $bundle;
        }

        return $byFqcn;
    }

    private function isBundleAlreadyInstalled($bundleFqcn, $checkCoreBundle = true)
    {
        if ('Claroline\CoreBundle\ClarolineCoreBundle' === $bundleFqcn && !$checkCoreBundle) {
            return true;
        }

        try {
            return $this->om->getRepository('ClarolineCoreBundle:Plugin')->findOneByBundleFQCN($bundleFqcn);
        } catch (TableNotFoundException $e) {
            // we're probably installing the platform because the database isn't here yet do... return false
            return false;
        }
    }
}
