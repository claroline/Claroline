<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TemplateCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:template:checker')
            ->setDescription('Search the unused templates in the CoreBundle view folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = array();
        $ds = DIRECTORY_SEPARATOR;
        $projectDir = $this->getContainer()->getParameter('kernel.root_dir')."{$ds}..{$ds}src";
        $viewFolder = "{$projectDir}{$ds}core/Claroline/CoreBundle/Resources/views";

        $projectIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($projectDir),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $viewIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewFolder),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($viewIterator as $item) {
            if ($item->isFile()) {
                $er = $this->check($projectIterator, $item);
                if ($er != null) {
                    $errors[] = $er;
                }
            }
        }

        foreach ($errors as $error) {
            $output->writeln("<bg=red>{$error}<bg=red>");
        }

        if (count($errors) == 0) {
            $output->writeln("<bg=green>No useless template found<bg=green>");
        }
    }

    /**
     * @todo: use regex (it's going to be a complex one)
     */
    private function check($projectIterator, \SplFileInfo $viewFile) {

        $ds = DIRECTORY_SEPARATOR;
        $toCut = str_replace('/app', '/src', $this->getContainer()->getParameter('kernel.root_dir'))."{$ds}core/";
        $shortName = str_replace($toCut, '', $viewFile->getRealPath());
        $parts = explode('/', $shortName);
        $withoutColon = array_shift($parts).array_shift($parts);
        foreach($parts as $part) {
            $withoutColon.='/'.$part;
        }
        $reverseWithoutColon = str_replace('/', '\\', $withoutColon);
        $shortName = str_replace('/Resources/views', '', $shortName);
        $parts = explode('/', $shortName);
        $shortName = array_shift($parts).array_shift($parts);
        $shortName.=":";
        if (count($parts) > 1) {
            $shortName.= array_shift($parts);
            $end = ":".array_pop($parts);
        } else {
            $shortName .= ':'.array_shift($parts);
            $end = '';
        }

        foreach ($parts as $part) {
            $shortName.= "/".$part;
        }
        $shortName.= $end;

        $reverseShortName = str_replace('/', '\\', $shortName);
        $escapedShortName = str_replace('\\', '\\\\', $reverseShortName);

        foreach ($projectIterator as $item) {
            if ($item->isFile()) {
                $file = file_get_contents($item->getRealPath());
                if (strpos($file, $shortName)) {
                    return null;
                }
                if (strpos($file, $reverseShortName)) {
                    return null;
                }
                if (strpos($file, $withoutColon)) {
                    return null;
                }
                if (strpos($file, $reverseWithoutColon)) {
                    return null;
                }
                if (strpos($file, $escapedShortName)) {
                    return null;
                }
            }
        }

        return "No occurences of
        {$shortName}
        {$reverseShortName}
        {$withoutColon}
        {$reverseWithoutColon}
        {$escapedShortName}
        were found";
    }
}