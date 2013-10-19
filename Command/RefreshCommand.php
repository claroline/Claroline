<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:refresh')
            ->setDescription('Installs/dumps the assets and empties the cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $refresher = $this->getContainer()->get('claroline.installation.refresher');
        $refresher->setOutput($output);
        $refresher->refresh($input->getOption('env'));
    }
}
