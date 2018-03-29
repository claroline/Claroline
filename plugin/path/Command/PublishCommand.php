<?php

namespace Innova\PathBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Publish Path into the Application.
 */
class PublishCommand extends AbstractPublishCommand
{
    protected function configure()
    {
        $this
            ->setName('innova:path:publish')
            ->setDescription('Publish selected paths')

            ->addOption('workspace', 'w', InputOption::VALUE_OPTIONAL, 'Workspace ID. Publish only Paths for this Workspace.')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Path ID. Publish only this Path.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force publish Paths which are not flagged to publish.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');

        $text = $force ? 'Publishing Paths into application with <info>force</info> option' : 'Publishing Paths into application';
        $output->writeln($text);

        if ($pathId = $input->getOption('path')) {
            // Need to publish a specific Path
            $path = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('InnovaPathBundle:Path\Path')->find($pathId);
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
                $output->writeln('<error>Unable to find Path referenced by ID : '.$pathId.'</error>');
            }
        } else {
            // Need to publish a list of Paths
            $paths = [];

            if ($workspaceId = $input->getOption('workspace')) {
                // Need to publish Paths for a specific Workspace
                $workspace = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Workspace\Workspace')->find($workspaceId);
                if (!empty($workspace)) {
                    // Workspace found => retrieve Paths
                    $paths = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('InnovaPathBundle:Path\Path')->findWorkspacePaths($workspace, !$force);
                } else {
                    // Workspace not found
                    $output->writeln('<error>Unable to find Workspace referenced by ID : '.$workspaceId.'</error>');
                }
            } else {
                // Need to publish all Paths
                $paths = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('InnovaPathBundle:Path\Path')->findPlatformPaths(!$force);
            }

            // Publish selected path
            $this->publish($paths, $output);
        }

        return $this;
    }
}
