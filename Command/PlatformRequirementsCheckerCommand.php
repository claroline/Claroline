<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class PlatformRequirementsCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:requirements')
            ->setDescription('Checks the platform requirements.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $requirements = $this->getContainer()->get('claroline.installation.requirements_checker')->check();

        foreach ($requirements['errors'] as $error) {
            $output->writeln("<bg=red>{$error}</bg=red>");
        }

        foreach ($requirements['warning'] as $warning) {
             $output->writeln("<bg=yellow>{$warning}<bg=yellow>");
        }

        foreach ($requirements['valid'] as $valid) {
             $output->writeln("<bg=green>{$valid}<bg=green>");
        }

        if (count($requirements['errors']) > 0) {
            exit(1);
        }
    }

}
