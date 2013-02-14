<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class RouteCheckerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:route:checker')
            ->setDescription('Search the unused route and order them in their different config.yml files');
        $this->addOption(
            'file',
            null,
            InputOption::VALUE_OPTIONAL,
            'Wich routing file do you want to be parsed ?'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ds = DIRECTORY_SEPARATOR;
        $projectDir = $this->getContainer()->getParameter('kernel.root_dir')."{$ds}..{$ds}src";
        $routingFolder = "{$projectDir}{$ds}core/Claroline/CoreBundle/Resources/config/routing";
        $fileName = $input->getOption('file');
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($projectDir),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        if ($fileName == null) {
            $errors = array();
            foreach (new \DirectoryIterator($routingFolder) as $fileInfo) {
                $er = $this->parseRoutingFile($fileInfo, $iterator);
                if ($er != null) {
                    $errors[] = $er;
                }
            }
        } else {
            $errors[] = $this->parseRoutingFile(new \SplFileInfo("{$projectDir}{$ds}..{$ds}$fileName"), $iterator);
        }

        foreach ($errors as $error) {
            foreach ($error['_route'] as $routeError) {
                $output->writeln($routeError);
            }
            foreach ($error['_controller'] as $ctrlError) {
                $output->writeln("<bg=red>{$ctrlError}</bg=red>");
            }
            foreach ($error['_missing'] as $ctrlError) {
                $output->writeln("<bg=magenta>{$ctrlError}</bg=magenta>");
            }
        }
    }

    private function parseRoutingFile(\SplFileInfo $fileInfo, $iterator)
    {
        $errors = array();

        if ($fileInfo->isFile()) {
            try {
                $this->order($fileInfo);
                $errors = $this->check($fileInfo, $iterator);
            } catch (ParseException $e) {
                printf("Unable to parse the YAML string: %s", $e->getMessage());
            }
        }

        return $errors;
    }

    private function order ($fileInfo)
    {
        $value = Yaml::parse($fileInfo->getRealPath());
        ksort($value);
        $yaml = Yaml::dump($value);
        file_put_contents($fileInfo->getRealPath(), $yaml);
    }

    private function check ($ymlFile, $iterator)
    {
        printf("Parsing {$ymlFile->getRealPath()}...\n");
        $routes = Yaml::parse($ymlFile->getRealPath());
        $errors = array();
        $errors['_route'] = array();
        $errors['_missing'] = array();
        $errors['_controller'] = array();

        foreach ($routes as $route => $value) {
            $found = false;

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile() && $fileInfo->getRealPath() !== $ymlFile->getRealPath()) {
                    $file = file_get_contents($fileInfo->getRealPath());
                    if (strpos($file, $route)) {
                        $found = true;
                    }
                }
            }
            if (!$found) {
                 $errors['_route'][] = "The string {$route} in {$ymlFile->getBaseName()} was never found";
            }

            try {
                $controllerName = $value['defaults']['_controller'];
                $method = $this->getContainer()
                    ->get('claroline.utilities.controller_name_parser')
                    ->parse($controllerName);
                $parts = explode('::', $method);
                if (!method_exists($parts[0], $parts[1])) {
                    $errors['_controller'][] =
                    "The controller {$method} defined in {$ymlFile->getBaseName()} doesn't exists";
                }
            } catch (\Exception $e) {
                $errors['_missing'][] = "No controller defined for route {$route}";
            }
        }

        return $errors;
    }
}
