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

class DumpImporterConfigurationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:importer:dump')
            ->setDescription('Dump an importer configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = $this->getContainer()->get('claroline.manager.transfert_manager')->dumpConfiguration();

        echo $content;
    }
} 