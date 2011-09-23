<?php

namespace Claroline\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use \Symfony\Component\Console\Input\InputInterface;

abstract class AbstractPluginCommand extends ContainerAwareCommand
{
    protected function resetCache(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('cache:clear');
        $input = new ArrayInput(
            array_merge(
                array(
                    'command' => 'cache:clear', // strange but doesn't work if removed
                    '--no-warmup' => true,
                ),
                $input->getArguments()
            )
        );
        $command->run($input, $output);
    }
}