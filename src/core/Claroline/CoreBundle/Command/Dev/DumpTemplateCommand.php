<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DumpTemplateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:dump_default')
            ->setDescription('Fills the database with a large amount of data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('dumping...');
        $ds = DIRECTORY_SEPARATOR;
        $templateFile = $this->getContainer()
            ->getParameter('claroline.param.templates_directory') . $ds. 'default.zip';
        $archive = new \ZipArchive();
        $archive->open($templateFile);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        var_export($parsedFile);
        $archive->close();
    }
}