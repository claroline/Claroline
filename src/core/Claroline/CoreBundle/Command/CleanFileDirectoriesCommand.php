<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\CoreBundle\Library\Security\PlatformRoles;

/**
 * Development command deleting the content of the "files", "test/files" and
 * "web/HTMLPage" directories. Only called by the automatic re-installation
 * script (bin/factory_install_dev.php). Note that in order to work, the user of
 * this command must have sufficient permissions on the mentionned directories.
 */
class CleanFileDirectoriesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:files:clean')
            ->setDescription('remove files in files directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileDirectory = realpath($this->getContainer()->getParameter('claroline.files.directory'));
        $output->writeln("cleaning {$fileDirectory}");
        $this->emptyDir($fileDirectory);

        $testFileDirectory = realpath("{$fileDirectory}/../test/files");
        $output->writeln("cleaning {$testFileDirectory}");
        $this->emptyDir($testFileDirectory);

        $htmlPageDirectory = realpath($this->getContainer()->getParameter('claroline.html_page.directory'));
        $output->writeln("cleaning {$htmlPageDirectory}");
        $this->emptyDir($htmlPageDirectory);

        $output->writeln("done");
    }

    private function emptyDir($dir)
    {
        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $item) {
            if ($item->isFile() && $item->getFileName() != 'placeholder' && $item->getFileName() != '.gitempty') {
                chmod($item->getPathname(), 0777);
                unlink($item->getPathname());
            }
            if ($item->isDir() && !$item->isDot() && $item->getFilename() != "tmp" && $item->getFilename() != "thumbs") {
                $this->emptyDir($item->getPathname());
                rmdir($item->getPathname());
            }
        }
    }
}
