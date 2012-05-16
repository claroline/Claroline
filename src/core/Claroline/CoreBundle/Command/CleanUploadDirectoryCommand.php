<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\CoreBundle\Library\Security\PlatformRoles;

class CleanUploadDirectoryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:files:clean')
        ->setDescription('remove files in files directory');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "cleaning {$this->getContainer()->getParameter('claroline.files.directory')}\n";
        $dir = $this->getContainer()->getParameter('claroline.files.directory');
        $this->emptyDir($dir);
        echo "cleaning {$this->getContainer()->getParameter('claroline.html_page.directory')}\n";
        $dir = $this->getContainer()->getParameter('claroline.html_page.directory');
        $this->emptyDir($dir);
        echo "cleaning {$this->getContainer()->getParameter('claroline.files.directory')}\n";
        $dir = $this->getContainer()->getParameter('claroline.files.directory');
        $dir.='/../test/files';
        $this->emptyDir($dir);   
        echo "done\n";
    }
    
    private function emptyDir($dir)
    {
         $iterator = new \DirectoryIterator($dir);
         
         foreach ($iterator as $item)
         {
             if($item->isFile() && $item->getFileName()!='placeholder' && $item->getFileName()!='.gitignore')
             {
                 chmod($item->getPathname(), 0777);
                 unlink($item->getPathname());
             }
             if($item->isDir() && ($item->isDot()==null) && $item->getFilename() !="tmp" && $item->getFilename()!="thumbs")
             {
                 $this->emptyDir($item->getPathname());
                 rmdir($item->getPathname());
             }
         }
    }
}
