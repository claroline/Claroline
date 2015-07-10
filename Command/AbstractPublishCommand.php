<?php

namespace Innova\PathBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Manager\PublishingManager;
use Innova\PathBundle\Entity\Path\Path;

abstract class AbstractPublishCommand extends Command
{
    /**
     * Object Manager
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * Publishing Manager
     * @var \Innova\PathBundle\Manager\PublishingManager
     */
    protected $publishingManager;

    /**
     * Class constructor
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param \Innova\PathBundle\Manager\PublishingManager $publishingManager
     */
    public function __construct(
        ObjectManager     $objectManager,
        PublishingManager $publishingManager)
    {
        parent::__construct();
        
        $this->objectManager     = $objectManager;
        $this->publishingManager = $publishingManager;
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
            if ($this->publishingManager->publish($path)) {
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
