<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Uninstalls all the plugins found in the "plugin" directory.
 */
class PluginUninstallAllCommand extends AbstractPluginCommand
{

    protected function configure()
    {
        $this->setName('claroline:plugin:uninstall_all')
            ->setDescription('Uninstalls all the plugins within "src/plugin".');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->walkPluginDirectory('uninstallPlugin', $output)) {
            $this->resetCache($output);
        }

        $output->writeln('Done');
    }
}