<?php

namespace Claroline\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

abstract class AbstractPluginCommand extends ContainerAwareCommand
{
    protected function resetCache(OutputInterface $output)
    {
        $command = $this->getApplication()->find('cache:clear');
        $input = new ArrayInput(array(
            'command' => 'cache:clear', // strange but doesn't work if removed
            '--no-warmup' => true,
        ));
        $command->run($input, $output);
    }
}