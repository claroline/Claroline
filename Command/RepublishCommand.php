<?php

namespace Innova\PathBundle\Command;

use Innova\PathBundle\Entity\Path\Path;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RepublishCommand extends ContainerAwareCommand
{
    private $pathRepo;
    private $pathPublishing;

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
        $this->pathRepo       = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('InnovaPathBundle:Path\Path');
        $this->pathPublishing = $this->getContainer()->get('innova_path.manager.publishing');

        $all = $input->getOption('all');

        $output->writeln('Republishing Paths');

        $paths = $this->pathRepo->findPublishedPath($all);

        if (empty($paths)) {
            // No paths to publish
            $output->writeln('Nothing to republish.');
        } else {
            // Loop through paths to publish them
            foreach ($paths as $path) {
                $this->publishPath($path, $output);
            }
        }

        return $this;
    }

    private function publishPath(Path $path, OutputInterface $output)
    {
        $datePublished = date('H:i:s');

        try {
            if ($this->pathPublishing->publish($path)) {
                $output->writeln('<comment>'.$datePublished.'</comment> <info>[ok]</info> '.$path->getResourceNode()->getName().' (ID = '.$path->getId().')');
            } else {
                $output->writeln('<comment>'.$datePublished.'</comment> <error>[error]</error> '.$path->getResourceNode()->getName().' (ID = '.$path->getId().')');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }

        return $this;
    }
}
