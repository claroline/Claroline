<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Claroline\CoreBundle\Library\Security\PlatformRoles;

class CreateThumbnailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:thumbnail:create')
        ->setDescription('thumbnail generation');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $thumbnailGeneration = $this->getContainer()->get('claroline.thumbnail.creator');
        $output->writeln("starting...");
        $thumbnailGeneration->parseAllAndGenerate();
        $outup->writeln("done");
    }
}
