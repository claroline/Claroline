<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deletes soft deleted old Scorm12/Scorm2004 resources.
 */
class DeleteOldScormCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:scorm:delete')
            ->setDescription('Deletes old Scorm12/Scorm2004 resources');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $resourceManager = $this->getContainer()->get('claroline.manager.resource_manager');
        $scorm12Repo = $om->getRepository('Claroline\ScormBundle\Entity\Scorm12Resource');
        $scorm2004Repo = $om->getRepository('Claroline\ScormBundle\Entity\Scorm2004Resource');
        $allScorm12 = $scorm12Repo->findAll();
        $allScorm2004 = $scorm2004Repo->findAll();
        $filesDir = $this->getContainer()->getParameter('claroline.param.files_directory');
        $scormResourcesDir = $this->getContainer()->getParameter('claroline.param.uploads_directory').
            DIRECTORY_SEPARATOR.
            'scormresources';

        $om->startFlushSuite();
        $i = 1;

        foreach ($allScorm12 as $scorm12) {
            $node = $scorm12->getResourceNode();

            if (!$node->isActive()) {
                $output->writeln('<info>Deleting resource ['.$node->getName().']...</info>');
                $hashName = $scorm12->getHashName();
                $scormArchiveFile = $filesDir.DIRECTORY_SEPARATOR.$hashName;
                $scormPath = $scormResourcesDir.DIRECTORY_SEPARATOR.$hashName;

                if (file_exists($scormArchiveFile)) {
                    unlink($scormArchiveFile);
                }
                if (file_exists($scormPath)) {
                    try {
                        $this->deleteFiles($scormPath);
                    } catch (\Exception $e) {
                        $output->writeln('<error>'.$e->getMessage().'</error>');
                    }
                }

                $resourceManager->delete($node);

                $output->writeln('<info>Resource deleted.</info>');

                if (0 === $i % 100) {
                    $output->writeln('<info>Flushing...</info>');
                    $om->forceFlush();
                }
                ++$i;
            }
        }
        foreach ($allScorm2004 as $scorm2004) {
            $node = $scorm2004->getResourceNode();

            if (!$node->isActive()) {
                $output->writeln('<info>Deleting resource ['.$node->getName().']...</info>');
                $hashName = $scorm2004->getHashName();
                $scormArchiveFile = $filesDir.DIRECTORY_SEPARATOR.$hashName;
                $scormPath = $scormResourcesDir.DIRECTORY_SEPARATOR.$hashName;

                if (file_exists($scormArchiveFile)) {
                    unlink($scormArchiveFile);
                }
                if (file_exists($scormPath)) {
                    try {
                        $this->deleteFiles($scormPath);
                    } catch (\Exception $e) {
                        $output->writeln('<error>'.$e->getMessage().'</error>');
                    }
                }

                $resourceManager->delete($node);

                $output->writeln('<info>Resource deleted.</info>');

                if (0 === $i % 100) {
                    $output->writeln('<info>Flushing...</info>');
                    $om->forceFlush();
                }
                ++$i;
            }
        }
        $om->endFlushSuite();
    }

    /**
     * Deletes recursively a directory and its content.
     *
     * @param $dirPath The path to the directory to delete
     */
    private function deleteFiles($dirPath)
    {
        foreach (glob($dirPath.DIRECTORY_SEPARATOR.'{*,.[!.]*,..?*}', GLOB_BRACE) as $content) {
            if (is_dir($content)) {
                $this->deleteFiles($content);
            } else {
                unlink($content);
            }
        }
        rmdir($dirPath);
    }
}
