<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Dev;

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
        $refresher = $this->getContainer()->get('Claroline\CoreBundle\Library\Installation\Refresher');
        $refresher->setOutput($output);
        $refresher->refresh($input->getOption('env'));
    }
}
