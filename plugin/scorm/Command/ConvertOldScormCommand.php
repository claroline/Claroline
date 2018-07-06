<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Converts old Scorm12/Scorm2004 resources to new Scorm resources.
 */
class ConvertOldScormCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:scorm:convert')
            ->setDescription('Converts old Scorm12/Scorm2004 resources to new Scorm resources');

        $this->addOption(
            'no-logs',
            'l',
            InputOption::VALUE_NONE,
            'When set to true, all associated logs are not copied'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $withoutLogs = $input->getOption('no-logs');
        $scormManager = $this->getContainer()->get('claroline.manager.scorm_manager');

        $output->writeln('<info>  Starting conversion of Scorm 1.2 resources...</info>');
        $scormManager->convertAllScorm12(!$withoutLogs);
        $output->writeln('<info>  Conversion of Scorm 1.2 resources is finished.</info>');

        $output->writeln('<info>  Starting conversion of Scorm 2004 resources...</info>');
        $scormManager->convertAllScorm2004(!$withoutLogs);
        $output->writeln('<info>  Conversion of Scorm 2004 resources is finished.</info>');
    }
}
