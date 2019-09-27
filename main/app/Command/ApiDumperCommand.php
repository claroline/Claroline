<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ApiDumperCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:api:dump')->setDescription('Dump the api doc as json');
        $this->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'The required format (json|yml)');
        $this->addOption('debug', 'd', InputOption::VALUE_NONE, 'debug mode (no output)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = $input->getOption('format') ?? 'json';
        $data = [];
        $classes = $this->getContainer()->get('Claroline\AppBundle\Routing\Finder')->getHandledClasses();

        foreach ($classes as $class) {
            $data[$class] = $this->getContainer()->get('Claroline\AppBundle\Routing\Documentator')->documentClass($class);
        }

        switch ($format) {
          case 'json': $string = json_encode($data, JSON_PRETTY_PRINT); break;
          case 'yml': $string = Yaml::dump($data); break;
        }

        if (!$input->getOption('debug')) {
            $output->writeln($string);
        }
    }
}
