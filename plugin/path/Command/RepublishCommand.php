<?php

namespace Innova\PathBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Force publishing for Paths which are already published in the application.
 */
class RepublishCommand extends AbstractPublishCommand
{
    protected function configure()
    {
        $this
            ->setName('innova:path:republish')
            ->setDescription('Republish paths')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Republish all paths, even these who have pending changes.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = $input->getOption('all');

        $output->writeln('Republishing Paths');

        $paths = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('InnovaPathBundle:Path\Path')->findPublishedPath($all);

        // Publish selected path
        $this->publish($paths, $output);

        return $this;
    }
}
