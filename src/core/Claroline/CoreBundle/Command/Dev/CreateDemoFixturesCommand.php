<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\CoreBundle\Tests\DataFixtures\LoadDemoFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;

class CreateDemoFixturesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:fixture:demo')
            ->setDescription('Load the demo fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Loading demo fixtures...');
        $fixture = new LoadDemoFixture();
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->setLogger(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );
        $fixture->load($em);
        $output->writeln('Done');
    }
}