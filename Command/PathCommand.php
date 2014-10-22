<?php

namespace Innova\PathBundle\Command;

use Innova\PathBundle\Entity\Path\Path;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PathCommand extends ContainerAwareCommand
{
    private $pathRepo;

    private $workspaceRepo;

    private $pathPublishing;

    protected function configure()
    {
        $this
            ->setName('innova:path:publish')
            ->setDescription('Publish selected paths')

            ->addOption('workspace', 'w', InputOption::VALUE_OPTIONAL, 'Workspace ID. Publish only Paths for this Workspace.')
            ->addOption('path',      'p', InputOption::VALUE_OPTIONAL, 'Path ID. Publish only this Path.')
            ->addOption('force',     'f', InputOption::VALUE_NONE,     'Force publish Paths which are not flagged to publish.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pathRepo       = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('InnovaPathBundle:Path\Path');
        $this->workspaceRepo  = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Workspace\Workspace');
        $this->pathPublishing = $this->getContainer()->get('innova_path.manager.publishing');

        $force = $input->getOption('force');

        $text = $force ? 'Publishing Paths into application with <info>force</info> option' : 'Publishing Paths into application';
        $output->writeln($text);

        if ($pathId = $input->getOption('path')) {
            // Need to publish a specific Path
            $path = $this->pathRepo->find($pathId);
            if (!empty($path)) {
                // Path found => publish it
                if (!$path->isPublished() || $path->isModified() || $force) {
                    // Path need publishing or force option is defined
                    $this->publishPath($path, $output);
                } else {
                    // Path doesn't need to be published
                    $output->writeln('<comment>Path is already published. Please run the operation with the --force option to force publishing</comment>');
                }
            } else {
                // Path not found
                $output->writeln('<error>Unable to find Path referenced by ID : ' . $pathId . '</error>');
            }
        } else {
            // Need to publish a list of Paths
            $paths = array ();

            if ($workspaceId = $input->getOption('workspace')) {
                // Need to publish Paths for a specific Workspace
                $workspace = $this->workspaceRepo->find($workspaceId);
                if (!empty($workspace)) {
                    // Workspace found => retrieve Paths
                    $paths = $this->pathRepo->findWorkspacePaths($workspace, !$force);
                } else {
                    // Workspace not found
                    $output->writeln('<error>Unable to find Workspace referenced by ID : ' . $workspaceId . '</error>');
                }
            } else {
                // Need to publish all Paths
                $paths = $this->pathRepo->findPlatformPaths(!$force);
            }

            if (empty($paths)) {
                // No paths to publish
                $output->writeln('Nothing to publish.');
            } else {
                // Loop through paths to publish them
                foreach ($paths as $path) {
                    $this->publishPath($path, $output);
                }
            }
        }

        return $this;
    }

    private function publishPath(Path $path, OutputInterface $output)
    {
        $datePublished = date('H:i:s');

        try {
            if ($this->pathPublishing->publish($path)) {
                $output->writeln('<comment>' . $datePublished . '</comment> <info>[ok]</info> ' . $path->getResourceNode()->getName() . ' (ID = ' . $path->getId() . ')');
            } else {
                $output->writeln('<comment>' . $datePublished . '</comment> <error>[error]</error> ' . $path->getResourceNode()->getName() . ' (ID = ' . $path->getId() . ')');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        return $this;
    }
}