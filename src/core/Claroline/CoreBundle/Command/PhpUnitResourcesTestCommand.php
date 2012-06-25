<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Development command launching the resources related tests.
 */
class PhpUnitResourcesTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:phpunit:resource')
            ->setDescription('run the resource test suite');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('beginning');
        $output->writeln("DirectoryControllerTest");
        system("phpunit -c app src/core/Claroline/CoreBundle/Tests/Controller/DirectoryControllerTest");
        $output->writeln("ResourceControllerTest");
        system("phpunit -c app src/core/Claroline/CoreBundle/Tests/Controller/ResourceControllerTest");
        $output->writeln("TextControllerTest");
        system("phpunit -c app src/core/Claroline/CoreBundle/Tests/Controller/TextControllerTest");
        $output->writeln("FileControllerTest");
        system("phpunit -c app src/core/Claroline/CoreBundle/Tests/Controller/FileControllerTest");
        $output->writeln("done");
    }
}