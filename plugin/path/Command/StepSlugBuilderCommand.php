<?php

namespace Innova\PathBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StepSlugBuilderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:steps:slug')
            ->setDescription('Rebuild the step slug');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->conn = $this->getContainer()->get('doctrine.dbal.default_connection');
        $sql = "
           UPDATE innova_step step SET slug = CONCAT(SUBSTR(step.title,1,100) , '-', step.id)
        ";
        $output->writeln($sql);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $output->writeln('Done !');
    }
}
