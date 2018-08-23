<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migration12HomeTabCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:migration12:home-tab')
            ->setDescription('This command allow you to rebuild trim the tabs html datas and stuff');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
