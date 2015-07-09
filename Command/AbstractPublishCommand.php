<?php

namespace Innova\PathBundle\Command;

use Innova\PathBundle\Entity\Path\Path;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractPublishCommand extends ContainerAwareCommand
{
    /**
     * @var \Innova\PathBundle\Repository\PathRepository
     */
    protected $pathRepo;

    /**
     * @var \Claroline\CoreBundle\Repository\WorkspaceRepository
     */
    protected $workspaceRepo;

    /**
     * @var \Innova\PathBundle\Manager\PublishingManager
     */
    protected $pathPublishing;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        if (null !== $container) {
            $this->pathRepo       = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('InnovaPathBundle:Path\Path');
            $this->workspaceRepo  = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Workspace\Workspace');
            $this->pathPublishing = $this->getContainer()->get('innova_path.manager.publishing');
        }
    }

    protected function publish(array $paths, OutputInterface $output)
    {
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

    protected function publishPath(Path $path, OutputInterface $output)
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
