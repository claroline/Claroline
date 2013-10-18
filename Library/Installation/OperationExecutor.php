<?php

namespace Claroline\CoreBundle\Library\Installation;

use Symfony\Component\HttpKernel\KernelInterface;
use Claroline\CoreBundle\Library\Installation\Plugin\Installer;
use Claroline\InstallationBundle\Manager\InstallationManager;
use Claroline\BundleRecorder\Handler\OperationHandler;
use Claroline\BundleRecorder\Operation;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.installation.operation_executor")
 */
class OperationExecutor
{
    private $kernel;
    private $baseInstaller;
    private $pluginInstaller;
    private $operationFile;
    private $logger;

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

    public function setOperationFile($operationFile)
    {
        $this->operationFile = $operationFile;
    }

    public function setLogger(\Closure $logger)
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
        $opHandler = new OperationHandler($this->operationFile, $this->logger);
        $bundles = $this->getBundlesByFqcn();

        foreach ($opHandler->getOperations() as $operation) {
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
