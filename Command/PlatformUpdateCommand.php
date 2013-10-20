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
        $executor = $this->getContainer()->get('claroline.installation.operation_executor');
        $executor->setLogger(function ($message) use ($output) {
            $output->writeln($message);
        });
        $executor->execute();
    }
}
