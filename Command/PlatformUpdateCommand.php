<?php

namespace Claroline\CoreBundle\Command;

use Claroline\BundleRecorder\Operation;
use Claroline\BundleRecorder\Handler\OperationHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;

/**
 * Updates, installs or uninstalls the core and plugin bundles, following
 * the operation order logged in *app/config/operations.xml* during
 * composer execution.
 */
class PlatformUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:update')
            ->setDescription('Updates, installs or uninstalls the claroline packages brought by composer.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Updating the platform...');
        $logger = function ($message) use ($output) {
            $output->writeln($message);
        };
        $kernel = $this->getContainer()->get('kernel');
        $baseInstaller = $this->getContainer()->get('claroline.installation.manager');
        $pluginInstaller = $this->getContainer()->get('claroline.plugin.installer');
        $baseInstaller->setLogger($logger);
        $pluginInstaller->setLogger($logger);
        $opHandler = new OperationHandler($kernel->getRootDir() . '/config/operations.xml', $logger);
        $bundles = $this->getBundlesByFqcn();

        foreach ($opHandler->getOperations() as $operation) {
            if ($operation->getType() === Operation::INSTALL) {
                if ($operation->getBundleType() === Operation::BUNDLE_CORE) {
                    $baseInstaller->install($bundles[$operation->getBundleFqcn()]);
                } else {
                    $pluginInstaller->install($bundles[$operation->getBundleFqcn()]);
                }
            } elseif ($operation->getType() === Operation::UPDATE) {
                if ($operation->getBundleType() === Operation::BUNDLE_CORE) {
                    $baseInstaller->install($bundles[$operation->getBundleFqcn()]);
                } else {
                    $pluginInstaller->install($bundles[$operation->getBundleFqcn()]);
                }
            } else {
                // remove...
            }
        }
    }

    private function getBundlesByFqcn()
    {
        $bundles = $this->getContainer()->get('kernel')->getBundles();
        $byFqcn = array();

        foreach ($bundles as $bundle) {
            $byFqcn[get_class($bundle)] = $bundle;
        }

        return $byFqcn;
    }
}
