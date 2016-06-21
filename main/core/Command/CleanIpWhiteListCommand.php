<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanIpWhiteListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:white_list:clean')
            ->setDescription('Cleans the ip white list file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('claroline.manager.ip_white_list_manager')->cleanWhiteList();
    }
}
