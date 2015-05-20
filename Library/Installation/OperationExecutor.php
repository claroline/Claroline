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

use Claroline\BundleRecorder\Log\LoggableTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Claroline\InstallationBundle\Manager\InstallationManager;
use Claroline\BundleRecorder\Handler\OperationHandler;
use Claroline\BundleRecorder\Operation;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.installation.operation_executor")
 *
 * Installs/updates platform bundles as mentioned in the operation
 * file (operations.xml) generated during composer execution.
 */
class OperationExecutor
{
    use LoggableTrait;

    private $kernel;
    private $baseInstaller;
    private $pluginInstaller;
    private $operationFile;

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
    }

    /**
     * @param string $operationFile
     */
    public function setOperationFile($operationFile)
    {
        $this->operationFile = $operationFile;
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

    public function execute()
    {
        $this->operationFile = $this->operationFile ?
            $this->operationFile :
            $this->kernel->getRootDir() . '/config/operations.xml';
        $operationsHandler = new OperationHandler($this->operationFile, $this->logger);
        $bundles = $this->getBundlesByFqcn();
        $operations = $operationsHandler->getOperations();

        /** @var \Claroline\BundleRecorder\Operation[] $orderedOperations */
        $orderedOperations = [];
        foreach ($operations as $operation) {
            if ($operation->getBundleType() === Operation::BUNDLE_CORE) {
                array_unshift($orderedOperations, $operation);
            } else {
                array_push($orderedOperations, $operation);
            }
        }

        foreach ($orderedOperations as $operation) {
            $installer = $operation->getBundleType() === Operation::BUNDLE_CORE ?
                $this->baseInstaller :
                $this->pluginInstaller;

            if ($operation->getType() === Operation::INSTALL) {
                $installer->install($bundles[$operation->getBundleFqcn()]);
            } elseif ($operation->getType() === Operation::UPDATE) {
                $installer->update(
                    $bundles[$operation->getBundleFqcn()],
                    $operation->getFromVersion(),
                    $operation->getToVersion()
                );
            } else {
                // remove or disable package
            }
        }

        rename($this->operationFile, $this->operationFile . '.bup');
    }

    private function getBundlesByFqcn()
    {
        $byFqcn = array();

        foreach ($this->kernel->getBundles() as $bundle) {
            $byFqcn[get_class($bundle)] = $bundle;
        }

        return $byFqcn;
    }
}
