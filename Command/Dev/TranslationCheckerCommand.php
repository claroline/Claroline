<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class TranslationCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:translation:checker')
            ->setDescription('Search the translations and order them in their different config.yml files');
        $this->addOption(
            'file',
            null,
            InputOption::VALUE_OPTIONAL,
            'Wich translation file do you want to be parsed ?'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ds = DIRECTORY_SEPARATOR;
        $projectDir = $this->getContainer()->getParameter('kernel.root_dir')."{$ds}..{$ds}src";
        $routingFolder = "{$projectDir}{$ds}core/Claroline/CoreBundle/Resources/translations";
        $fileName = $input->getOption('file');

        if ($fileName == null) {
            foreach (new \DirectoryIterator($routingFolder) as $fileInfo) {
                $this->parseTranslationFile($fileInfo);
            }
        } else {
            $this->parseTranslationFile(new \SplFileInfo("{$projectDir}{$ds}..{$ds}$fileName"));
        }
    }

    private function parseTranslationFile(\SplFileInfo $fileInfo)
    {
        if ($fileInfo->isFile()) {
            try {
                $this->order($fileInfo);
            } catch (ParseException $e) {
                printf("Unable to parse the YAML string: %s", $e->getMessage());
            }
        }
    }

    private function order ($fileInfo)
    {
        $value = Yaml::parse($fileInfo->getRealPath());
        ksort($value);
        $yaml = Yaml::dump($value);
        file_put_contents($fileInfo->getRealPath(), $yaml);
    }
}
